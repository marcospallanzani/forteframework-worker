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
     * When this flag is set to true, all errors raised by this action
     * should stop the execution of the main process. This logic is
     * implemented in the runner class (instances of AbstractRunner).
     *
     * @var bool
     */
    protected $isFatal = false;

    /**
     * When this flag is set to true, the system will check the result of
     * the action. If it is successful (no errors and successful result),
     * then the execution will continue. If not successful, an exception
     * will be thrown. This flag can be used to express complex condition
     * where one action is executed only if a previous action executed
     * correctly and returned its positive case result.
     * E.g. modify a configuration key X in a file Y, if file Y exists.
     * If the pre-check on file Y existence executed with no errors but
     * returned false (i.e. the file does not exist), we shouldn't run
     * the dependent action "modify configuration key X2.
     *
     * @var bool
     */
    protected $isSuccessRequired = false;

    /**
     * Validate the given action result. This method returns true if the
     * given ActionResult instance has a result value that is considered
     * as a positive case by this AbstractAction subclass instance.
     * E.g. if the aim of the current action is to check that a given key X
     * is defined in a given array Y, then the expected positive result is a
     * boolean flag equal to true if the key X exists in the array Y.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of the current AbstractAction subclass
     * instance.
     *
     * @return bool True if the given ActionResult instance has a result value
     * that is considered as a positive case by this AbstractAction subclass
     * instance; false otherwise.
     */
    public abstract function validateResult(ActionResult $actionResult): bool;

    /**
     * Apply the subclass action.
     *
     * @param ActionResult $actionResult The action result object to register
     * all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields
     * regarding failures and result content.
     *
     */
    protected abstract function apply(ActionResult $actionResult): ActionResult;

    /**
     * Validate this AbstractAction subclass instance using a validation logic
     * specific to the current instance.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected abstract function validateInstance(): bool;

    /**
     * Whether this AbstractAction subclass instance is valid or not.
     *
     * @return bool True if this AbstractAction subclass instance was
     * well configured; false otherwise.
     *
     * @throws ActionException If this AbstractAction subclass instance
     * was not well configured.
     */
    public function isValid(): bool
    {
        // By catching here all exceptions, we are sure that the
        // isValid method only throws ActionException instances.
        $isValid = false;
        try {
            $isValid = $this->validateInstance();
        } catch (\Exception $exception) {
            // We catch any exception coming from the child class
            // And we convert them to an ActionException
            if ($exception instanceof ActionException) {
                throw $exception;
            }
            $this->throwActionException(
                new ActionResult($this),
                "Action not valid: '%s'. Reason: '%s'.",
                $this,
                $exception->getMessage()
            );
        }

        return $isValid;
    }

    /**
     * Run the subclass action with pre-validation AND with
     * pre- and post-run actions too.
     *
     * @return ActionResult The ActionResult instance representing
     * the result of the just-run action.
     *
     * @throws ActionException
     */
    public function run(): ActionResult
    {
        $actionResult = new ActionResult($this);

        // We don't catch here the exceptions thrown by the isValid() method.
        // A bad configured action should always be considered as FATAL.
        if ($this->isValid()) {

            // We run the pre-run actions
            $this->runBeforeActions($actionResult);

            // We run the logic specific to this AbstractAction subclass instance
            // AND we save its result in the ActionResult wrapper object
            try {
                $actionResult = $this->apply($actionResult);
            } catch (\Exception $exception) {
                /**
                 * If the given exception is an instance of ActionException, we have to check
                 * if there are some fatal and/or success-required children failures: if so,
                 * then we throw the parent exception.
                 */
                $actionHasCriticalFailures = false;
                $caughtActionResult = new ActionResult($this);
                if ($exception instanceof ActionException) {
                    $actionHasCriticalFailures = $exception->checkForCriticalFailures();
                    $caughtActionResult = $exception->getActionResult();
                }

                $actionFailure = $this->getActionException(
                    $caughtActionResult, "Action failed: '%s'. Reason: '%s'.", $this, $exception->getMessage()
                );
//TODO WE NEED TO HANDLE THE FAILED CHILDREN ACTIONS
//                if ($caughtActionResult) {
//                    $actionFailure->addFailedChildAction($caughtActionResult);
//                }

                if ($actionHasCriticalFailures || $this->isFatal || $this->isSuccessRequired) {
                    throw $actionFailure;
                } else {
                    $actionResult->addActionFailure($actionFailure);
                }
            }

            // If the result of the last execution of the run method returned a negative case,
            // in case this action is flagged as 'success required', we need to throw an exception
            if (!$this->validateResult($actionResult) && $this->isSuccessRequired) {
                $this->throwActionException(
                    $actionResult, "The following required action returned a negative result: '%s'.", $this
                );
            }

            // We run the post-run actions
            $this->runAfterActions($actionResult);
        }

        return $actionResult;
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
     * Ree
     * @return bool True if this AbstractAction subclass instance is fatal; false otherwise.
     */
    public function isFatal(): bool
    {
        return $this->isFatal;
    }

    /**
     * Set the fatal flag with the desired value.
     *
     * @param bool $isFatal The desired fatal flag value.
     *
     * @return AbstractAction
     */
    public function setIsFatal(bool $isFatal): self
    {
        $this->isFatal = $isFatal;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSuccessRequired(): bool
    {
        return $this->isSuccessRequired;
    }

    /**
     * @param bool $isSuccessRequired
     *
     * @return AbstractAction
     */
    public function setIsSuccessRequired(bool $isSuccessRequired): self
    {
        $this->isSuccessRequired = $isSuccessRequired;

        return $this;
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

    /**
     * Run the pre-run actions and return a list failed AbstractAction instances.
     *
     * @param ActionResult $actionResult The parent ActionResult instance.
     *
     * @throws ActionException A fatal pre-run action has failed.
     */
    protected function runBeforeActions(ActionResult &$actionResult): void
    {
        foreach ($this->beforeActions as $action) {
            try {
                if ($action instanceof AbstractAction) {
                    // We run the pre-run action
                    $preActionResult = $action->run();

                    /**
                     * If it was not run successfully OR the result was negative
                     * (e.g. condition not met, change not applied), then we add
                     * its result to the list of pre-run failed action results.
                     */
                    if (!$preActionResult->isSuccessfulRun() || !$action->validateResult($preActionResult)) {
                        $actionResult->addFailedPreRunActionResult($preActionResult);
                    }
                }
            } catch (ActionException $actionException) {
                // We handle the caught ActionException for the just-run pre-run failed action
                $this->handleActionException($actionResult, $actionException, "A pre-run action");

                /**
                 * If no exception thrown at the previous line, it means that the just-run
                 * pre-run action is NOT FATAL. In this case, we mark the just-run action
                 * as failed and continue the execution of the foreach loop.
                 */
                $actionResult->addFailedPreRunActionResult($actionException->getActionResult());
            }
        }
    }

    /**
     * Run the post-run actions and return a list failed AbstractAction instances.
     *
     * @param ActionResult $actionResult The parent ActionResult instance.
     *
     * @throws ActionException A fatal post-run action has failed.
     */
    protected function runAfterActions(ActionResult &$actionResult): void
    {
        foreach ($this->afterActions as $action) {
            try {
                if ($action instanceof AbstractAction) {
                    // We run the post-run action
                    $postActionResult = $action->run();

                    /**
                     * If it was not run successfully OR the result was negative
                     * (e.g. condition not met, change not applied), then we add
                     * its result to the list of post-run failed action results.
                     */
                    if (!$postActionResult->isSuccessfulRun() || !$action->validateResult($postActionResult)) {
                        $actionResult->addFailedPostRunActionResult($postActionResult);
                    }
                }
            } catch (ActionException $actionException) {
                // We handle the caught ActionException for the just-run post-run failed action
                $this->handleActionException($actionResult, $actionException, "A post-run action");

                /**
                 * If no exception thrown at the previous line, it means that the just-run
                 * post-run action is NOT FATAL. In this case, we mark the just-run action
                 * as failed and continue the execution of the foreach loop.
                 */
                $actionResult->addFailedPostRunActionResult($actionException->getActionResult());
            }
        }
    }

    /**
     * Handle the given ActionException instance for the given AbstractAction subclass
     * instance. It also handles the "isFatal" and "isSuccessRequired" for the given
     * AbstractAction subclass instance.
     *
     * @param ActionResult $actionResult The AbstractAction subclass instance to handle.
     * @param ActionException $actionException The ActionException instance to handle.
     * @param string $actionDescription A prefix error description that will be
     * appended at the beginning of the error message, which describes the given
     * AbstractAction subclass instance.
     *
     * @throws ActionException
     */
    protected function handleActionException(
        ActionResult $actionResult,
        ActionException $actionException,
        string $actionDescription = ""
    ): void
    {
        // We set the caught error as a failure of the given just-run action
        $actionResult->addActionFailure($actionException);

        // If FATAL action failed, we throw the error
        // so the action can be interrupted
        if ($actionResult->getAction()->isFatal() || $actionResult->getAction()->isSuccessRequired()) {
            $this->throwActionExceptionWithChildren(
                $actionResult,
                [$actionException->getActionResult()],
                (!empty($actionDescription) ? $actionDescription : "An action") . " failed with error '%s'.",
                $actionException->getMessage()
            );
        }
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
     *
     * @deprecated Should we deprecate this function?
     */
//TODO REMOVE THIS FUNCTION
    protected function checkFileExists(string $filePath, bool $raiseError = true): bool
    {
        $fileExists = false;

        try {
            // We check if the origin file exists
            $fileExists = $this->fileExists($filePath, $raiseError);
        } catch (WorkerException $workerException) {
            $this->throwActionException($this, $workerException->getMessage());
        }

        return $fileExists;
    }
}
