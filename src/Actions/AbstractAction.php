<?php

namespace Forte\Worker\Actions;

use Forte\Stdlib\ClassAccessTrait;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Helpers\Collection;
use Forte\Worker\Helpers\FileTrait;
use Forte\Worker\Helpers\StringParser;
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
     * This ID represents a unique identifier for an AbstractAction subclass
     * instance, so that it can be identified in a multi-level chain of actions.
     *
     * @var string
     */
    protected $uniqueExecutionId = "";

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
     * AbstractAction constructor.
     */
    public function __construct()
    {
        $classNameSpace = explode('\\', static::class);
        $this->uniqueExecutionId = StringParser::getRandomUniqueId(array_pop($classNameSpace));
    }

    /**
     * Whether this AbstractAction subclass instance is valid or not.
     *
     * @return bool True if this AbstractAction subclass instance was
     * well configured; false otherwise.
     *
     * @throws ValidationException If this AbstractAction subclass instance
     * was not well configured.
     */
    public final function isValid(): bool
    {
        // By catching here all exceptions, we are sure that the isValid
        // method only throws ValidationException instances.
        $isValid = false;
        try {
            $isValid = $this->validateInstance();
        } catch (\Exception $exception) {
            // We catch any exception coming from the child class
            // And we convert them to an ValidationException
            if ($exception instanceof ValidationException) {
                throw $exception;
            }
            $this->throwValidationException($this, $exception->getMessage());
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
    public final function run(): ActionResult
    {
        $actionResult = new ActionResult($this);

        $actionResult->setStartTimestamp();

        try {
            // We don't catch here the exceptions thrown by the isValid() method.
            // A bad configured action should always be considered as FATAL.
            if ($this->isValid()) {

                // We run the pre-run actions
                $this->runBeforeActions($actionResult);

                // We run the logic specific to this AbstractAction subclass instance
                // AND we save its result in the ActionResult wrapper object
                $actionResult = $this->apply($actionResult);

                // We run the post-run actions
                $this->runAfterActions($actionResult);
            }
        } catch (\Exception $exception) {

            /**
             * If the given exception is an instance of ActionException, we have to check
             * if there are some fatal and/or success-required children failures: if so,
             * then we throw the parent exception.
             */
            $childFatalError = false;
            if ($exception instanceof ValidationException) {
                // If it's a validation error, we don't apply any modifications
                // and we continue with the remaining checks (is fatal?)
                $actionFailure = $exception;
            } else if ($exception instanceof ActionException) {
                /**
                 * We have caught a child-action failure. In this case, we add the caught
                 * child-action exception to the list of failed children actions of the
                 * current action failure object.
                 */
                $actionFailure = $this->getActionException(
                    $this, "Action failure caused by one failed child action."
                );
                $actionFailure->addChildFailure($exception);

                // We detected a child failure: in this case we check if it's fatal: if yes,
                // it should trigger the exception for the parent action as well
                $childFatalError = $exception->hasFatalFailures();

            } else {
                $actionFailure = $this->getActionException($this, $exception->getMessage());
            }

            // We set a negative result for the current ActionResult instance
            $this->setNegativeResult($actionResult);

            // If we caught a fatal ActionException thrown by a child process
            // OR the current action is fatal, then we throw the exception
            if ($childFatalError || $this->isFatal) {
                throw $actionFailure;
            } else {
                $actionResult->addActionFailure($actionFailure);
            }
        }

        // If the result of the last execution of the run method returned a negative case,
        // in case this action is flagged as 'success required', we need to throw an exception
        if (!$this->validateResult($actionResult) && $this->isSuccessRequired) {
            /**
             * We throw a new ActionException with an appropriate "success-required" message if:
             * - the current result has failures (i.e. the result was negative because of an error);
             * - the current result has NO failure (i.e. the action executed correctly, but the result was
             *   negative, e.g. check condition "file-exists" always runs successfully but return a negative
             *   result if the given file does no exist);
             */
            $actionFailures = $actionResult->getActionFailures();
            if (count($actionFailures) === 1) {
                throw current($actionFailures);
            } else {
                $this->throwActionExceptionWithChildren(
                    $actionResult->getAction(),
                    $actionResult->getActionFailures(),
                    "Positive result expected (action marked as 'success-required')."
                );
            }
        }

        $actionResult->setEndTimestamp();

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
     * Return an array representation of this AbstractAction subclass instance.
     *
     * @return array An array representation of this AbstractAction subclass instance.
     */
    public function toArray(): array
    {
        $variables['action_type'] = get_class($this);
        $variables['action_description'] = $this->stringify();
        return array_merge(
            $variables,
            Collection::variablesToArray(get_object_vars($this))
        );
    }

    /**
     * Return the unique execution ID for this AbstractAction subclass instance.
     *
     * @return string
     */
    public function getUniqueExecutionId(): string
    {
        return $this->uniqueExecutionId;
    }

    /**
     * Validate the given action result. This method returns true if the
     * given ActionResult instance has a result value that is considered
     * as a positive case by this AbstractAction subclass instance.
     * E.g. if the aim of the current action is to check that a given key X
     * is defined in a given array Y, then the expected positive result is a
     * boolean flag equal to true, if the key X exists in the array Y.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of the current AbstractAction subclass
     * instance.
     *
     * @return bool True if the given ActionResult instance has a result value
     * that is considered as a positive case by this AbstractAction subclass
     * instance; false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        // Default case: we assume that the result can be casted to a boolean value
        return (bool) $actionResult->getResult();
    }

    /**
     * Change the given ActionResult instance so that it has a value, which is
     * considered as the negative case by this AbstractAction subclass instance.
     * E.g. if the aim of the current action is to check that a given key X
     * is defined in a given array Y, then the expected negative result is a
     * boolean flag equal to false, if the key X exists in the array Y.
     *
     * @param ActionResult $actionResult The ActionResult instance to be modified
     * with a negative-case value.
     */
    public function setNegativeResult(ActionResult &$actionResult): void
    {
        $actionResult->setResult(false);
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
     * Check if the given nested actions are valid.
     *
     * @param array $nestedActions The list of nested actions to check.
     * @param string $expectedActionClass The expected class name
     * of the nested action instances.
     *
     * @return bool True if all the given actions are valid.
     *
     * @throws ValidationException If one or more actions in the
     * given list are not valid.
     */
    protected function validateNestedActionsList(array $nestedActions, string $expectedActionClass): bool
    {
        // Check if the given nested actions are well configured
        $wrongNestedActions = array();
        foreach ($nestedActions as $nestedAction) {
            /** @var AbstractAction $nestedAction */
            // We check if the given actions are of the expected type
            if (!is_a($nestedAction, $expectedActionClass)) {
                $wrongNestedActions[] = $this->getActionException(
                    $this,
                    "Unsupported nested action type [%s] registered in class [%s]. " .
                    "Nested actions should be instances of [%s]",
                    (is_object($nestedAction) ? get_class($nestedAction) : gettype($nestedAction)),
                    static::class,
                    $expectedActionClass
                );
            }

            try {
                // We check if the current action is valid
                $nestedAction->isValid();
            } catch (ValidationException $actionException) {
                $wrongNestedActions[] = $actionException;
            }
        }

        // We check if some nested actions are not valid: if so, we throw an exception
        if ($wrongNestedActions) {
            $this->throwValidationExceptionWithChildren(
                $this,
                $wrongNestedActions,
                "One or more nested actions are not valid."
            );
        }

        return true;
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
            // We initialize the result object for the current pre-run action
            $preActionResult = new ActionResult($action);
            try {
                if ($action instanceof AbstractAction) {
                    // We run the pre-run action
                    $preActionResult->setStartTimestamp();
                    $preActionResult = $action->run();

                    /**
                     * If it was not run successfully OR the result was negative
                     * (e.g. condition not met, change not applied), then we add
                     * its result to the list of pre-run failed action results.
                     */
                    if (!$preActionResult->isSuccessfulRun() || !$action->validateResult($preActionResult)) {
                        $actionResult->addFailedPreRunActionResult($preActionResult);
                    } else {
                        $actionResult->addPreRunActionResult($preActionResult);
                    }
                }
            } catch (ActionException $actionException) {
                // We handle the child ActionException (check if fatal  and add it
                // to the list of action failures)
                $this->handleChildActionException(
                    $preActionResult,
                    $actionResult,
                    $actionException,
                    "Action failure caused by a fatal pre-run failed action."
                );

                // If no exception is thrown, we add the pre-run result object
                // to the list of failed pre-run action results
                $actionResult->addFailedPreRunActionResult($preActionResult);
            }

//TODO SHOULD WE HANDLE HERE THE CASE IS SUCCESS REQUIRED SEPARATELY FROM THE FATAL CASE?
            $preActionResult->setEndTimestamp();
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
            // We initialize the result object for the current pre-run action
            $postActionResult = new ActionResult($action);
            try {
                if ($action instanceof AbstractAction) {
                    // We run the post-run action
                    $postActionResult->setStartTimestamp();
                    $postActionResult = $action->run();

                    /**
                     * If it was not run successfully OR the result was negative
                     * (e.g. condition not met, change not applied), then we add
                     * its result to the list of post-run failed action results.
                     */
                    if (!$postActionResult->isSuccessfulRun() || !$action->validateResult($postActionResult)) {
                        $actionResult->addFailedPostRunActionResult($postActionResult);
                    } else {
                        $actionResult->addPostRunActionResult($postActionResult);
                    }
                }
            } catch (ActionException $actionException) {
                // We handle the child ActionException (check if fatal and add it
                // to the list of action failures)
                $this->handleChildActionException(
                    $postActionResult,
                    $actionResult,
                    $actionException,
                    "Action failure caused by a fatal post-run failed action."
                );

                // If no exception is thrown, we add the post-run result object
                // to the list of failed post-run action results
                $actionResult->addFailedPostRunActionResult($postActionResult);
            }
//TODO SHOULD WE HANDLE HERE THE CASE IS SUCCESS REQUIRED SEPARATELY FROM THE FATAL CASE?
            $postActionResult->setEndTimestamp();
        }
    }

    /**
     * Run the given list of nested actions and apply, for each one of them, the given callable
     * (defined in this AbstractAction subclass instance).
     *
     * @param ActionResult $parentActionResult The parent action ActionResult instance. This instance
     * will be modified by this action, with the final result, the children failures, etc.
     * @param array $nestedRunActions A list of nested AbstractAction subclass instances to run.
     * @param NestedActionCallbackInterface $nestedActionCallback A callable that implements the run logic
     * specific to the current AbstractAction subclass instance.
     * @param mixed $nestedRunContent The content to be used by the nested action run method, if required.
     * @param array $runActionsOptions The options to run the given list of AbstractAction subclass instances
     * (options for action X must be registered in this array with the same key as the one used to register
     * action X in the parameter $nestedRunActions).
     *
     * @throws ActionException
     */
    protected function applyWithNestedRunActions(
        ActionResult &$parentActionResult,
        array &$nestedRunActions,
        NestedActionCallbackInterface $nestedActionCallback,
        &$nestedRunContent = null,
        array &$runActionsOptions = array()
    ): void
    {
        // We check all configured conditions for the configured file
        $failedNestedActions = array();
        foreach ($nestedRunActions as $actionKey => $nestedRunAction) {
            // We create the action result object for the current check
            $nestedActionResult = new ActionResult($nestedRunAction);
            try {
                $currentActionOptions = [];
                if (array_key_exists($actionKey, $runActionsOptions)) {
                    // By reference
                    $currentActionOptions = &$runActionsOptions[$actionKey];
                }
                $nestedActionCallback->runNestedAction(
                    $nestedRunAction,
                    $nestedActionResult,
                    $failedNestedActions,
                    $nestedRunContent,
                    $currentActionOptions
                );
            } catch (\Exception $exception) {
                // If not an ActionException, it means that the nested action throw an unexpected error.
                // In this case, we convert this error to an ActionException instance and we continue.
                if (!$exception instanceof ActionException) {
                    $actionException = $this->getActionException($nestedRunAction, $exception->getMessage());
                } else {
                    $actionException = $exception;
                }

                // We handle the caught ActionException for the just-run failed check: if fatal,
                // we throw it again so that it can be caught and handled in the run method
                if ($nestedRunAction->isFatal()) {
                    throw $actionException;
                }

                // If the current nested action is marked as "success-required", it means that
                /**
                 * If we get to this point, it means that the just-checked failed
                 * check is NOT FATAL; in this case, we add to the list of failed
                 * checks and we continue the execution of the current foreach loop.
                 */
                $nestedActionResult->addActionFailure($actionException);
                $failedNestedActions[] = $nestedActionResult;
            }
        }

        /**
         * We check the results of the nested actions. If some errors occurred,
         * we have to check if they are fatal or not. If fatal, we throw
         * a global action exception;
         *
         * The other scenarios should be handled in the child class.
         */
        $globalResult = true;
        if ($failedNestedActions) {
            // We set the global result to false, as some children actions failed
            // either because of a negative result or because of an exception
            $globalResult = false;

            // We generate a failure instance to handle the fatal/success-required cases
            $exception = $this->getActionException($parentActionResult->getAction(), "One or more sub-action failed.");
            foreach ($failedNestedActions as $failedNestedAction) {
                foreach ($failedNestedAction->getActionFailures() as $failure) {
                    $exception->addChildFailure($failure);
                }
            }

            // If not fatal, we add the current failure to the list of failures for this action
            $parentActionResult->addActionFailure($exception);
        }

        $parentActionResult->setResult($globalResult);
    }

    /**
     * Handle the given child ActionException instance. It also handles the "isFatal"
     * case of the child AbstractAction subclass instance.
     *
     * @param ActionResult $childActionResult
     * @param ActionResult $parentActionResult
     * @param ActionException $childActionException
     * @param string $parentActionFailureMessage
     *
     * @throws ActionException
     */
    protected function handleChildActionException(
        ActionResult &$childActionResult,
        ActionResult &$parentActionResult,
        ActionException $childActionException,
        string $parentActionFailureMessage
    ): void
    {
        // We set the caught error as a failure of the given child action result
        $childActionResult->addActionFailure($childActionException);

        // If FATAL action failed, we throw the error, so that the main action can be interrupted
        if ($childActionResult->getAction()->isFatal()) {
            $this->throwActionExceptionWithChildren(
                $parentActionResult->getAction(),
                [$childActionException],
                $parentActionFailureMessage
            );
        }
    }
}
