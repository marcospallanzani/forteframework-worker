<?php

namespace Forte\Worker\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\ValidationException;

/**
 * Class IfStatement.
 *
 * @package Forte\Worker\Actions\Conditionals
 */
class IfStatement extends AbstractAction
{
    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var array
     */
    protected $runActions = [];

    /**
     * @var AbstractAction
     */
    protected $defaultAction;

    /**
     * IfStatement constructor.
     *
     * @param array $statementGroups A list of Condition Action - Run Action to register.
     *
     * @throws ConfigurationException If the given array is not well built.
     */
    public function __construct(array $statementGroups = array())
    {
        parent::__construct();
        $this->addStatements($statementGroups);
    }

    /**
     * Add an if-statement to the list of conditional actions to be run.
     *
     * @param AbstractAction $conditionAction The condition to be met to run the specified run-action.
     * @param AbstractAction $runAction The run-action to be run, if the specified condition is met.
     *
     * @return IfStatement
     */
    public function addStatement(AbstractAction $conditionAction, AbstractAction $runAction): self
    {
        /**
         * We have to mark the condition and the run actions as FATAL, so that, in case of error,
         * an exception will be thrown and caught in the AbstractAction run method. The idea is to
         * stop the execution of an if-elseif statement, if an error occurred in the execution of
         * its condition and action blocks. If we don't mark these two actions as fatal, an error
         * (e.g. validation error) will silently modify the main action result and set it to its
         * negative case. In the case of an if-else statement, we want the final result to be
         * modified only if the condition and run actions executed without errors.
         *
         * On top of that, we mark the condition action as NON-SUCCESS-REQUIRED, as a negative
         * result of the condition action should result in the execution of the next registered
         * if-elseif block (or the default block) and not in a thrown error.
         */
        $runAction->setIsFatal(true);
        $conditionAction
            ->setIsFatal(true)
            ->setIsSuccessRequired(false)
        ;

        $conditionUniqueId = $conditionAction->getUniqueExecutionId();
        $this->runActions[$conditionUniqueId] = $runAction;
        $this->conditions[] = $conditionAction;

        return $this;
    }

    /**
     * Register the given list of statements. Each entry should be a couple Condition Action - Run Action
     * (AbstractAction subclass instances).
     *
     * e.g.
     * [
     *  [AbstractAction $condition1, AbstractAction $run1],
     *  [AbstractAction $condition2, AbstractAction $run2],
     *  ...
     *  ...
     * ]
     *
     * @param array $statementGroups A list of Condition Action - Run Action to register.
     *
     * @return IfStatement
     *
     * @throws ConfigurationException If the given array is not well built.
     */
    public function addStatements(array $statementGroups): self
    {
        foreach ($statementGroups as $statementGroup) {
            if (is_array($statementGroup) && count($statementGroup) === 2) {
                $runAction = array_pop($statementGroup);
                $conditionAction = array_pop($statementGroup);
                if ($conditionAction instanceof AbstractAction && $runAction instanceof AbstractAction) {
                    $this->addStatement($conditionAction, $runAction);
                    continue;
                }
            }
            $this->throwConfigurationException($this, "The given statements list is not well formed.");
        }

        return $this;
    }

    /**
     * Add the default action to be run, if the registered statements are not met. The default condition
     * corresponds to the -else statement of an if-else statement. If no statements are specified, this
     * action will be run in any case.
     *
     * @param AbstractAction $defaultAction The action to be run, if the registered statements are not met.
     *
     * @return IfStatement
     */
    public function addDefaultStatement(AbstractAction $defaultAction): self
    {
        $this->defaultAction = $defaultAction;

        return $this;
    }

    /**
     * Return a human-readable string representation of this implementing class instance.
     *
     * @return string A human-readable string representation of this implementing class instance.
     */
    public function stringify(): string
    {
        $message = "Run the following chain of if-else statements: " . PHP_EOL;
        foreach ($this->conditions as $key => $condition) {
            $message .= sprintf(
                "IF [%s] THEN [%s]; " . PHP_EOL,
                $condition,
                $this->runActions[$condition->getUniqueExecutionId()]
            );
        }

        if ($this->defaultAction instanceof AbstractAction) {
            $message .= sprintf("DEFAULT CONDITION [%s]", $this->defaultAction);
        }

        return $message;
    }

    /**
     * Apply the subclass action.
     *
     * @param ActionResult $actionResult The action result object to register all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields regarding failures and result content.
     *
     * @throws \Exception
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        try {
            $result = null;

            foreach ($this->conditions as $condition) {
                if ($condition instanceof AbstractAction) {
                    // We set the variables used in the catch block
                    $runningAction = $condition;

                    // We run the condition first
                    $runningResult = $runningAction->run();

                    // We add the just-run action result to the list of nested action results
                    // of the main ActionResult instance
                    $actionResult->addActionResult($runningResult);

                    // If the result is positive, then we need to run the correspondent run-action
                    if ($runningAction->validateResult($runningResult)) {
                        // The condition action ran successfully and returned a positive result:
                        // In this case, we run its equivalent run action
                        $conditionUniqueId = $runningAction->getUniqueExecutionId();
                        if (array_key_exists($conditionUniqueId, $this->runActions)) {
                            $runningAction = $this->runActions[$runningAction->getUniqueExecutionId()];
                            if ($runningAction instanceof AbstractAction) {
                                // We run the action associated to the positive condition
                                $this->runAndValidateAction($runningAction, $actionResult);
                            }
                        }
                    }
                }
            }

            // If, at this point the main action result is still unset, we try to run the default action
            $runningAction = $this->defaultAction;
            if ($actionResult->getResult() === null && $runningAction instanceof AbstractAction) {
                // We run the default action
                $this->runAndValidateAction($runningAction, $actionResult);
            }
        } catch (\Exception $exception) {
            /**
             * If not an ActionException, it means that the running action threw an unexpected error. In this
             * case, we convert this error to an ActionException instance and we exit the loop. We exit the
             * loop, because we assume that a chain of if-else statements is executed correctly only if no
             * errors occurred.
             *
             * Like in a programming language, if a syntax error happens in a chain of if-else, the execution
             * of a if (or elseif) block is stopped, i.e. the execution does not go on to the next elseif block.
             *
             * Here, we also rethrow the exception so that it can be caught in the AbstractAction::run() method
             * and treated accordingly (e.g. is fatal, is success required, etc.).
             */
            if (!$exception instanceof ActionException) {
                $actionException = $this->getActionException($runningAction, $exception->getMessage());
            } else {
                $actionException = $exception;
            }

            // We throw the exception so that it can be caught in the parent::run() method
            throw $actionException;
        }

        return $actionResult;
    }

    /**
     * Run the given child action and set its result in the given parent action result.
     *
     * @param AbstractAction $action The action to be run.
     * @param ActionResult $globalActionResult The global action result instance.
     *
     * @throws ActionException
     */
    protected function runAndValidateAction(
        AbstractAction &$action,
        ActionResult &$globalActionResult
    ): void
    {
        // We run the action
        $actionResult = $action->run();

        // We add the just-run action result to the list of nested action results
        // of the main ActionResult instance
        $globalActionResult->addActionResult($actionResult);

        // We set the global result according to the just-run action result
        if ($action->validateResult($actionResult)) {
            $globalActionResult->setResult(true);
        } else {
            $globalActionResult->setResult(false);
        }
    }

    /**
     * Validate this AbstractAction subclass instance using a validation logic specific to the current instance.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        $wrongActions = [];
        try {
            // We validate each registered AbstractAction subclass instance.
            foreach ($this->conditions as $condition) {
                // We validate the condition action
                $condition->isValid();
                // We validate the corresponding run action
                if (array_key_exists($condition->getUniqueExecutionId(), $this->runActions)) {
                    $this->runActions[$condition->getUniqueExecutionId()]->isValid();
                }
            }

            // We validate the default action, if set
            if ($this->defaultAction instanceof AbstractAction) {
                $this->defaultAction->isValid();
            }
        } catch (ValidationException $validationException) {
            $wrongActions[] = $validationException;
        }

        // If errors were caught, we throw a new exception with all the caught ones as children failures
        if ($wrongActions) {
            $this->throwValidationExceptionWithChildren(
                $this,
                $wrongActions,
                "One or more of the registered conditions are not valid."
            );
        }

        return true;
    }
}
