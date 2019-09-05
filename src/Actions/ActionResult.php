<?php

namespace Forte\Worker\Actions;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Helpers\ClassAccessTrait;

/**
 * Class ActionResult. This class wraps the status of a given AbstractAction
 * subclass instance with information about its result, its failures and all
 * the failures of its pre- and post-run actions.
 *
 * @package Forte\Worker\Actions
 */
class ActionResult
{
    use ClassAccessTrait;

    /**
     * Statuses constants
     */
    const SUCCESS_WITH_PRE_RUN_FAILURES         = "success_with_pre_run_failures";
    const SUCCESS_WITH_POST_RUN_FAILURES        = "success_with_post_run_failures";
    const SUCCESS_WITH_PRE_POST_RUN_FAILURES    = "success_with_pre_post_run_failures";
    const SUCCESS_NO_FAILURES                   = "success_no_failures";
    const FAILED_WITH_PRE_RUN_FAILURES          = "failed_with_pre_run_failures";
    const FAILED_WITH_POST_RUN_FAILURES         = "failed_with_post_run_failures";
    const FAILED_WITH_PRE_POST_RUN_FAILURES     = "failed_with_pre_post_run_failures";
    const FAILED                                = "failed";

    /**
     * @var AbstractAction
     */
    protected $action;

    /**
     * @var mixed
     */
    protected $result;

    /**
     * List of all failures for the AbstractAction subclass instance
     * wrapped in this ActionResult instance. It is a list of all the
     * exception objects with all the error details that caused the
     * action failure. Multiple failures are possible for one given
     * action.
     *
     * @var array
     */
    protected $actionFailures = [];

    /**
     * List of all pre-run ActionResult instances that failed.
     * Multiple failures are possible for one given parent action.
     *
     * @var array
     */
    protected $failedPreRunActionResults = [];

    /**
     * List of all post-run ActionResult instances that failed.
     * Multiple failures are possible for one given parent action.
     *
     * @var array
     */
    protected $failedPostRunActionResults = [];

    /**
     * ActionResult constructor.
     *
     * @param AbstractAction $action
     */
    public function __construct(AbstractAction $action)
    {
        $this->action = clone $action;
    }

    /**
     * Return the status of the last execution of the AbstractAction
     * subclass instance wrapped by this ActionResult instance.
     *
     * @return string
     */
    public function getStatus(): string
    {
        if (count($this->actionFailures)) {
            if (count($this->failedPreRunActionResults)) {
                if (!count($this->failedPostRunActionResults)) {
                    return self::FAILED_WITH_PRE_RUN_FAILURES;
                }
                return self::FAILED_WITH_PRE_POST_RUN_FAILURES;
            } else if (count($this->failedPostRunActionResults)) {
                return self::FAILED_WITH_POST_RUN_FAILURES;
            }
            return self::FAILED;
        } else {
            if (count($this->failedPreRunActionResults)) {
                if (!count($this->failedPostRunActionResults)) {
                    return self::SUCCESS_WITH_PRE_RUN_FAILURES;
                }
                return self::SUCCESS_WITH_PRE_POST_RUN_FAILURES;
            } else if (count($this->failedPostRunActionResults)) {
                return self::SUCCESS_WITH_POST_RUN_FAILURES;
            }
            return self::SUCCESS_NO_FAILURES;
        }
    }

    /**
     * Return true if the last execution of the AbstractAction subclass
     * instance, wrapped by this ActionResult instance, was successful
     * (no failures at all - i.e. no action failures, no pre-run and
     * no post-run actions failures); false otherwise.
     *
     * @return bool True if the last execution of the AbstractAction
     * subclass instance, wrapped by this ActionResult instance, was
     * successful (no failures at all - i.e. no action failures, no
     * pre-run and no post-run actions failures); false otherwise.
     */
    public function isSuccessfulRun(): bool
    {
        if ($this->getStatus() === self::SUCCESS_NO_FAILURES) {
            return true;
        }
        return false;
    }

    /**
     * Return the AbstractAction subclass instance wrapped
     * by this ActionResult instance.
     *
     * @return AbstractAction The AbstractAction subclass instance wrapped
     * by this ActionResult instance.
     */
    public function getAction(): AbstractAction
    {
        return $this->action;
    }

    /**
     * Return the action final result.
     *
     * @return mixed The action final result.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set the action final result.
     *
     * @param mixed $result The action final result.
     *
     * @return self
     */
    public function setResult($result): self
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Return a list of all action failures for the AbstractAction
     * subclass instance wrapped by this ActionResult instance.
     *
     * @return array The action failures.
     */
    public function getActionFailures(): array
    {
        return $this->actionFailures;
    }

    /**
     * Add the given action failure to this ActionResult instance.
     *
     * @param ActionException $actionFailure The ActionException instance to add.
     */
    public function addActionFailure(ActionException $actionFailure): void
    {
        $this->actionFailures[] = $actionFailure;
    }

    /**
     * Return the list of all failed pre-run AbstractAction subclass instances.
     *
     * @return array A list of failed pre-run AbstractAction subclass instances.
     */
    public function getFailedPreRunActionResults(): array
    {
        return $this->failedPreRunActionResults;
    }

    /**
     * Add the given ActionResult instance to the list of failed pre-run ActionResult instances.
     *
     * @param ActionResult $failedActionResult The pre-run ActionResult instance to add.
     */
    public function addFailedPreRunActionResult(ActionResult $failedActionResult): void
    {
        $this->failedPreRunActionResults[] = clone $failedActionResult;
    }

    /**
     * Return the list of all failed post-run ActionResult instances.
     *
     * @return array A list of failed post-run AbstractAction subclass instances.
     */
    public function getFailedPostRunActionResults(): array
    {
        return $this->failedPostRunActionResults;
    }

    /**
     * Add the given ActionResult instance to the list of failed post-run ActionResult instances.
     *
     * @param ActionResult $failedActionResult The post-run ActionResult instance to add.
     */
    public function addFailedPostRunActionResult(ActionResult $failedActionResult): void
    {
        $this->failedPostRunActionResults[] = clone $failedActionResult;
    }

    /**
     * Check if some critical failures were found in the failures tree of the AbstractAction
     * subclass instance, wrapped by this ActionResult instance.
     *
     * @return bool True if some critical failures were found in the failures tree of the
     * AbstractAction subclass instance, wrapped by this ActionResult instance.
     */
    public function hasCriticalFailures(): bool
    {
        return $this->checkForCriticalFailures($this);
    }

    /**
     * Check if some critical failures were found in the failures tree of the
     * given ActionResult instance.
     *
     * An action has some failures in its failures tree if:
     *
     * IF the action itself
     * AND/OR one or more of its pre-run actions (and all their nested actions)
     * AND/OR one or more of its post-run actions (and all their nested actions)
     * HAVE failures, which are marked as FATAL or SUCCESS-REQUIRED.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked.
     *
     * @return bool True if some critical failures were found in the failures tree
     * of the given ActionResult instance.
     */
    public function checkForCriticalFailures(ActionResult $actionResult): bool
    {
        // We first check the main action: if some critical failures were found, we can return true
        if($this->checkForCriticalActionFailures($actionResult)) {
            return true;
        }

        // We check the pre- and post-run actions
        $prePostRunActions = array_merge($this->failedPreRunActionResults, $this->failedPostRunActionResults);
        foreach ($prePostRunActions as $failedPreRunAction) {
            if ($this->checkForCriticalActionFailures($failedPreRunAction)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check each action failures and return true if some critical failures were found.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked.
     *
     * @return bool True if some critical failures were found.
     */
    protected function checkForCriticalActionFailures(ActionResult $actionResult): bool
    {
        foreach ($actionResult->getActionFailures() as $actionFailure) {
            /** @var ActionException $actionFailure */
            $currentAction = $actionFailure->getActionResult()->getAction();
            if ($currentAction->isSuccessRequired() || $currentAction->isFatal()) {
                return true;
            } else {
                foreach ($actionFailure->getActionResult()->getActionFailures() as $failure) {
                    /** @var ActionException $failure */
                    return $this->checkForCriticalFailures($failure->getActionResult());
                }
            }
        }
        return false;
    }
}
