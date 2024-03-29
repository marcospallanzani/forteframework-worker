<?php

namespace Forte\Worker\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\StringHelper;

/**
 * Class Loop.
 *
 * @package Forte\Worker\Actions\Conditionals
 */
class ForEachLoop extends AbstractAction
{
    /**
     * @var array
     */
    protected $actions = [];

    /**
     * ForEachLoop constructor.
     *
     * @param array $actions The actions to be run by this ForEachLoop.
     *
     * @throws ConfigurationException If the given array is not well built.
     */
    public function __construct(array $actions = [])
    {
        parent::__construct();
        $this->addActions($actions);
    }

    /**
     * Add the given actions list to the list of registered actions.
     * Only AbstractAction subclass instances are accepted.
     *
     * @param array $actions The actions to add.
     *
     * @return $this
     *
     * @throws ConfigurationException
     */
    public function addActions(array $actions): self
    {
        foreach ($actions as $action) {
            if ($action instanceof AbstractAction) {
                $this->addAction($action);
                continue;
            }
            $this->throwConfigurationException(
                $this,
                "Invalid action detected. Found [%s]. AbstractAction subclass instance expected.",
                StringHelper::stringifyVariable($action)
            );
        }

        return $this;
    }

    /**
     * Add the given AbstractAction subclass instance to this foreach loop.
     *
     * @param AbstractAction $action The AbstractAction subclass instance to
     * add to this foreach loop.
     *
     * @return $this
     */
    public function addAction(AbstractAction $action): self
    {
        $this->actions[$action->getUniqueExecutionId()] = $this->getActionForBlockExecution($action);

        return $this;
    }

    /**
     * Return the list of registered actions.
     *
     * @return array The list of registered actions.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        $message = "Run the following sequence of actions: " . PHP_EOL;
        foreach ($this->actions as $action) {
            $message .= $action . PHP_EOL;
        }

        return $message;
    }

    /**
     * Apply the subclass action.
     *
     * @param ActionResult $actionResult The action result object to register
     * all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields
     * regarding failures and result content.
     *
     * @throws \Exception An error occurred while executing one of the configured
     * actions.
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        foreach ($this->actions as $action) {
            // We run the action
            /** @var AbstractAction $action */
            $currentActionResult = $action->run();
            $actionResult->addActionResult($currentActionResult);
        }

        /**
         * The loop executed without errors and we can set the result to its positive case.
         * In case this action is marked as "success-required", the AbstractAction::run()
         * method will handle that case, once all nested, pre- and post-run actions have
         * executed.
         */
        return $actionResult->setResult(true);
    }

    /**
     * Validate this AbstractAction subclass instance using a validation logic
     * specific to the current instance.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        $wrongActions = [];

        // We validate each registered AbstractAction subclass instance.
        foreach ($this->actions as $action) {
            try {
                if ($action instanceof AbstractAction) {
                    $action->isValid();
                } else {
                    $this->throwWorkerException(
                        "Invalid action detected. Found [%s]. AbstractAction subclass instance expected.",
                        StringHelper::stringifyVariable($action)
                    );
                }
            } catch (WorkerException $workerException) {
                $wrongActions[] = $workerException;
            }
        }

        // If errors were caught, we throw a new exception with all the caught ones as children failures
        if ($wrongActions) {
            $this->throwValidationExceptionWithChildren(
                $this,
                $wrongActions,
                "One or more of the registered actions are not valid."
            );
        }

        return true;
    }

    /**
     * Clone and modify the given AbstractAction subclass instance so that it can be executed in a
     * foreach loop. It sets the cloned action as FATAL, so that, in case of error,an exception will
     * be thrown and caught in the AbstractAction::run() method. The idea is to stop the execution
     * of a foreach loop, if an error occurred in the execution of any of its blocks. We also set the
     * cloned action severity with a level higher than "SUCCESS-REQUIRED", as a negative action result
     * should be accepted as a possible result.
     *
     * @param AbstractAction $blockAction The action to be modified for the execution of a foreach
     * iteration.
     *
     * @return AbstractAction The modified action to be used in a foreach iteration.
     */
    protected function getActionForBlockExecution(AbstractAction $blockAction): AbstractAction
    {
        $action = clone $blockAction;
        $action->setActionSeverity(self::EXECUTION_SEVERITY_FATAL);

        return $action;
    }
}
