<?php

namespace Forte\Worker\Actions;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\FileTrait;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class AbstractAction.
 *
 * @package Forte\Worker\Actions
 */
abstract class AbstractAction implements ValidActionInterface
{
    use ClassAccessTrait, FileTrait, ThrowErrorsTrait;

    /**
     * List of AbstractAction subclass instances to be run
     * before the current AbstractAction subclass instance.
     *
     * @var array
     */
    protected $beforeActions = array();

    /**
     * List of AbstractAction subclass instances to be run
     * after the current AbstractAction subclass instance.
     *
     * @var array
     */
    protected $afterActions = array();

    /**
     * Apply the subclass action.
     *
     * @return bool True if the action implemented by this AbstractAction
     * subclass instance was successfully applied; false otherwise.
     *
     * @throws ActionException
     */
    protected abstract function apply(): bool;

    /**
     * Run the subclass action with pre-validation
     * AND with pre- and post-run actions too.
     *
     * @return bool True if this AbstractAction subclass
     * instance has been successfully applied; false otherwise.
     *
     * @throws ActionException
     */
    public function run(): bool
    {
        $result = false;

        if ($this->isValid()) {

            // We run the pre-run actions
            $this->runAndReportBeforeActions(true);

            $result = $this->apply();

            // We run the post-run actions
            $this->runAndReportAfterActions(true);
        }

        return $result;
    }

    /**
     * Add the given AbstractAction subclass instance to the list of
     * pre-run actions.
     *
     * @param AbstractAction $action The AbstractAction subclass instance
     * to be added to the list of pre-run actions.
     *
     * @return AbstractAction
     */
    public function addBeforeAction(AbstractAction $action): self
    {
        $this->beforeActions[] = $action;

        return $this;
    }

    /**
     * Add the given AbstractAction subclass instance to the list of
     * post-run actions.
     *
     * @param AbstractAction $action The AbstractAction subclass instance
     * to be added to the list of post-run actions.
     *
     * @return AbstractAction
     */
    public function addAfterAction(AbstractAction $action): self
    {
        $this->afterActions[] = $action;

        return $this;
    }

    /**
     * Run the pre-run actions and return a list failed
     * AbstractAction instances.
     *
     * @return array List of failed pre-run actions.
     */
    protected function runBeforeActions(): array
    {
        $failedActions = array();
        foreach ($this->beforeActions as $action) {
            try {
                if ($action instanceof AbstractAction && !$action->run()) {
                    $failedActions[] = new ActionException($action, "Action not successful.");
                }
            } catch (WorkerException $workerException) {
//TODO CHECK HERE IF ACTION IS MARKED AS FATAL OR NON-FATAL: IF FATAL, THROW EXCEPTION, IF NON-FATAL, THEN SAVE THE EXCEPTION AND CONTINUE THE EXECUTION
                $failedActions[] = new ActionException($action, sprintf(
                    "Action failed with error '%s'.",
                    $workerException->getMessage()
                ));
            }
        }
        return $failedActions;
    }

    /**
     * Run the post-run actions and return a list failed
     * AbstractAction instances.
     *
     * @return array List of failed post-run actions.
     */
    protected function runAfterActions(): array
    {
        $failedActions = array();
        foreach ($this->afterActions as $action) {
            try {
                if ($action instanceof AbstractAction && !$action->run()) {
                    $failedActions[] = new ActionException($action, "Action not successful.");
                }
            } catch (WorkerException $workerException) {
//TODO CHECK HERE IF ACTION IS MARKED AS FATAL OR NON-FATAL: IF FATAL, THROW EXCEPTION, IF NON-FATAL, THEN SAVE THE EXCEPTION AND CONTINUE THE EXECUTION
                $failedActions[] = new ActionException($action, sprintf(
                    "Action failed with error '%s'.",
                    $workerException->getMessage()
                ));
            }
        }

        return $failedActions;
    }

    /**
     * Run the configured pre-run actions and report all the occurred errors.
     *
     * @param bool $throwException Whether an exception, for failed actions,
     * should be thrown OR a string representation of them should be returned.
     *
     * @return string A string representation of the failed actions, in case the
     * thrownException flag is true.
     *
     * @throws ActionException
     */
    protected function runAndReportBeforeActions($throwException = false): string
    {
        // We run the pre-run actions
        $failedActions = $this->runBeforeActions();
//TODO REFACTOR THE WAY WE RENDER THIS ERROR MESSAGE.. WE SHOULD ADD A LIST OF FAILED CHILDREN ACTIONS TO THE ActionException class
        $message = "";
        if ($failedActions) {
            $message = "The following pre-run actions have failed: ";
            foreach ($failedActions as $failedAction) {
                if ($failedAction instanceof ActionException) {
                    $message .= sprintf(
                        "%s. FAILED ACTIONS INFO: %s. |||| ",
                        $failedAction->getAction(),
                        $failedAction->getMessage()
                    );
                }
            }
//TODO SET HERE THE ACTION DEPENDENCIES THAT FAILED FOR THE CURRENT ACTION
            if ($throwException) {
                $this->throwActionException($this, $message);
            }
        }
//TODO RETURN THE EXCEPTION INSTEAD?
        return $message;
    }

    /**
     * Run the configured post-run actions and report all the occurred errors.
     *
     * @param bool $throwException Whether we should throw an exception for the failed
     * actions OR return a string representation of them.
     *
     * @return string A string representation of the failed actions, in case the
     * thrownException flag is true.
     *
     * @throws ActionException
     */
    protected function runAndReportAfterActions($throwException = false): string
    {
        // We run the post-run actions
        $failedActions = $this->runAfterActions();
//TODO REFACTOR THE WAY WE RENDER THIS ERROR MESSAGE.. WE SHOULD ADD A LIST OF FAILED CHILDREN ACTIONS TO THE ActionException class
        $message = "";
        if ($failedActions) {
            $message = "The following post-run actions have failed: ";
            foreach ($failedActions as $failedAction) {
                if ($failedAction instanceof ActionException) {
                    $message .= sprintf(
                        "%s. FAILED ACTIONS INFO: %s. |||| ",
                        $failedAction->getAction(),
                        $failedAction->getMessage()
                    );
                }
            }
//TODO SET HERE THE ACTION DEPENDENCIES THAT FAILED FOR THE CURRENT ACTION
            if ($throwException) {
                $this->throwActionException($this, $message);
            }
        }
//TODO RETURN THE EXCEPTION INSTEAD?
        return $message;
    }



    /**
     * Check if the given file path exists or not; if it does not exist,
     * an ActionException will be thrown.
     *
     * @param string $filePath The file path to be checked.
     * @param bool $raiseError Whether an exception should be thrown if
     * the file does not exist.
     *
     * @return bool Returns true if the given file path points to an
     * existing file; false otherwise.
     *
     * @throws ActionException If the file does not exist,
     * an ActionException will be thrown.
     */
    protected function checkFileExists(string $filePath, bool $raiseError = true): bool
    {
        try {
            // We check if the origin file exists
            return $this->fileExists($filePath, $raiseError);
        } catch (WorkerException $workerException) {
            $this->throwActionException($this, $workerException->getMessage());
        }
    }

    /**
     * Return a string representation of this AbstractAction subclass instance.
     *
     * @return false|string A string representation of this AbstractAction
     * subclass instance.
     */
    public function __toString()
    {
        return static::stringify();
    }
}
