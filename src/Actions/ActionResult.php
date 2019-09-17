<?php

namespace Forte\Worker\Actions;

use Forte\Stdlib\ClassAccessTrait;
use Forte\Stdlib\DateUtils;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;

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
     * List of all action result instances created by the run action of
     * AbstractAction subclass instance wrapped by this class.
     *
     * This is used if the wrapped action has nested actions or is
     * a conditional action.
     *
     * In case that the wrapped action does not have any nested actions,
     * or is not a conditional action, then this array is not used.
     *
     * @var array
     */
    protected $actionsResults = [];

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
     * List of all pre-run ActionResult instances that were successful.
     * Multiple failures are possible for one given parent action.
     *
     * @var array
     */
    protected $preRunActionResults = [];

    /**
     * List of all post-run ActionResult instances that were successful.
     * Multiple failures are possible for one given parent action.
     *
     * @var array
     */
    protected $postRunActionResults = [];

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
     * @var int
     */
    protected $startTimestamp;

    /**
     * @var int
     */
    protected $endTimestamp;

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
     * Return a list of all action results. This list is used to save
     * all action results of the nested actions of the AbstractAction
     * subclass instance wrapped by this ActionResult instance.
     *
     * @return array
     */
    public function getActionResults(): array
    {
        return $this->actionsResults;
    }

    /**
     * Add the given ActionResult instance to the list of nested action
     * results.
     *
     * @param ActionResult $actionResult The ActionResult instance to add
     * to the list of nested action results.
     *
     * @return $this
     */
    public function addActionResult(ActionResult $actionResult): self
    {
        $this->actionsResults[$actionResult->getActionUniqueExecutionId()] = clone $actionResult;

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
     *
     * @return ActionResult
     */
    public function addActionFailure(ActionException $actionFailure): self
    {
        $this->actionFailures[] = $actionFailure;

        return $this;
    }

    /**
     * Return the list of all successful pre-run AbstractAction subclass instances.
     *
     * @return array A list of successful pre-run AbstractAction subclass instances.
     */
    public function getPreRunActionResults(): array
    {
        return $this->preRunActionResults;
    }

    /**
     * Add the given ActionResult instance to the list of successful pre-run ActionResult instances.
     *
     * @param ActionResult $actionResult The pre-run ActionResult instance to add.
     *
     * @return ActionResult
     */
    public function addPreRunActionResult(ActionResult $actionResult): self
    {
        $this->preRunActionResults[$actionResult->getActionUniqueExecutionId()] = clone $actionResult;

        return $this;
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
     * @param ActionResult $actionResult The pre-run ActionResult instance to add.
     *
     * @return ActionResult
     */
    public function addFailedPreRunActionResult(ActionResult $actionResult): self
    {
        $this->failedPreRunActionResults[$actionResult->getActionUniqueExecutionId()] = clone $actionResult;

        return $this;
    }

    /**
     * Return the list of all successful post-run ActionResult instances.
     *
     * @return array A list of successful post-run AbstractAction subclass instances.
     */
    public function getPostRunActionResults(): array
    {
        return $this->postRunActionResults;
    }

    /**
     * Add the given ActionResult instance to the list of successful post-run ActionResult instances.
     *
     * @param ActionResult $actionResult The post-run ActionResult instance to add.
     *
     * @return ActionResult
     */
    public function addPostRunActionResult(ActionResult $actionResult): self
    {
        $this->postRunActionResults[$actionResult->getActionUniqueExecutionId()] = clone $actionResult;

        return $this;
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
     * @param ActionResult $actionResult The post-run ActionResult instance to add.
     *
     * @return ActionResult
     */
    public function addFailedPostRunActionResult(ActionResult $actionResult): self
    {
        $this->failedPostRunActionResults[$actionResult->getActionUniqueExecutionId()] = clone $actionResult;

        return $this;
    }

    /**
     * Set the start timestamp for this ActionResult instance.
     */
    public function setStartTimestamp(): void
    {
        $this->startTimestamp = microtime(true);
    }

    /**
     * Set the end timestamp for this ActionResult instance.
     */
    public function setEndTimestamp(): void
    {
        $this->endTimestamp = microtime(true);
    }

    /**
     * Return the unique execution id of the AbstractAction subclass
     * instance wrapped by this ActionResult instance.
     *
     * @return string The unique execution id of the AbstractAction
     * subclass instance wrapped by this ActionResult instance.
     */
    public function getActionUniqueExecutionId(): string
    {
        return $this->action->getUniqueExecutionId();
    }

    /**
     * Convert this ActionResult instance to an array.
     *
     * @param bool $runMode If true, all fields related to the execution
     * of the AbstractAction subclass instance wrapped by this ActionResult
     * instance, will be included.
     *
     * @return array An array representation of this ActionResult instance.
     */
    public function toArray(bool $runMode = true): array
    {
        $array = [];

        // We add the action
        $array['action'] = $this->action->toArray();

        if ($runMode) {
            // Start and end timestamp
            if ($this->startTimestamp) {
                $array['start_timestamp'] = $this->startTimestamp;
                $array['start_date'] = DateUtils::formatMicroTime($this->startTimestamp);
            }
            if ($this->endTimestamp) {
                $array['end_timestamp'] = $this->endTimestamp;
                $array['end_date'] = DateUtils::formatMicroTime($this->endTimestamp);
            }

            // The global status
            $array['execution_status'] = $this->getStatus();

            // The result
            if ($this->result instanceof AbstractAction) {
                $array['result'] = $this->result->toArray();
            } else {
                $array['result'] = $this->result;
            }

            // The main action failures
            $array['main_action_failures'] = [];
            foreach ($this->actionFailures as $failure) {
                if ($failure instanceof WorkerException) {
                    $array['main_action_failures'][] = $failure->toArray();
                }
            }

            // The pre-run action results
            $array['pre_run_action_results'] = [];
            foreach ($this->failedPreRunActionResults as $preRunActionResult) {
                if ($preRunActionResult instanceof ActionResult) {
                    $array['pre_run_action_results'][] = $preRunActionResult->toArray();
                }
            }
            foreach ($this->preRunActionResults as $preRunActionResult) {
                if ($preRunActionResult instanceof ActionResult) {
                    $array['pre_run_action_results'][] = $preRunActionResult->toArray();
                }
            }

            // The pre-run action results
            $array['post_run_action_results'] = [];
            foreach ($this->failedPostRunActionResults as $postRunActionResult) {
                if ($postRunActionResult instanceof ActionResult) {
                    $array['post_run_action_results'][] = $postRunActionResult->toArray();
                }
            }
            foreach ($this->postRunActionResults as $postRunActionResult) {
                if ($postRunActionResult instanceof ActionResult) {
                    $array['post_run_action_results'][] = $postRunActionResult->toArray();
                }
            }
        }

        return $array;
    }
}
