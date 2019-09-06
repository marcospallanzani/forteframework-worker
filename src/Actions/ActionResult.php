<?php

namespace Forte\Worker\Actions;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Helpers\StringParser;

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
     * Set the start timestamp for this ActionResult instance.
     */
    public function setStartTimestamp(): void
    {
        $this->startTimestamp = time();
    }

    /**
     * Set the end timestamp for this ActionResult instance.
     */
    public function setEndTimestamp(): void
    {
        $this->endTimestamp = time();
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
     * Convert this ActionResult instance to an array.
     *
     * @return array An array representation of this ActionResult instance.
     */
    public function toArray(): array
    {
        $array = [];

        // Start and end timestamp
        if ($this->startTimestamp) {
            $array['start_timestamp'] = $this->startTimestamp;
            $array['start_date'] = date('Y-m-d H:i:s', $this->startTimestamp);
        }
        if ($this->endTimestamp) {
            $array['end_timestamp'] = $this->endTimestamp;
            $array['end_date'] = date('Y-m-d H:i:s', $this->endTimestamp);
        }

        // We add the action
        $array['action'] = $this->action->toArray();

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
            if ($failure instanceof ActionException) {
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

        // The pre-run action results
        $array['post_run_action_results'] = [];
        foreach ($this->failedPostRunActionResults as $postRunActionResult) {
            if ($postRunActionResult instanceof ActionResult) {
                $array['post_run_action_results'][] = $postRunActionResult->toArray();
            }
        }

        return $array;
    }

    /**
     * Export this ActionResult instance to a file. It convert this instance to an appropriate
     * file content, according to the given content type. The supported content types are the
     * FileParser constants starting with "CONTENT_TYPE_".
     *
     * @param string $contentType The desired content type (FileParser constants starting with
     * "CONTENT_TYPE").
     * @param string $exportDirPath The desired export directory.
     *
     * @throws WorkerException
     */
    public function exportToFile(
        string $contentType = FileParser::CONTENT_TYPE_JSON,
        string $exportDirPath = ""
    ): void
    {
        // We check the given parameters
        if (!empty($exportDirPath)) {
            $exportDirPath = rtrim($exportDirPath, DIRECTORY_SEPARATOR);
        } else {
            $exportDirPath = ".";
        }

        // We define a default name
        $fileName = "action_result_" . time();
        $fileExtension = FileParser::getFileExtensionByContentType($contentType);
        if ($fileExtension) {
            $fileName .= '.' . $fileExtension;
        } else {
            // It means that the given content type is not supported by the FileParser class.
            // In this case, we set it by default to array.
            $contentType = FileParser::CONTENT_TYPE_ARRAY;
            $fileName .= '.php';
        }
        $filePath = $exportDirPath . DIRECTORY_SEPARATOR . $fileName;

        // We convert this object to an array
        $actionResultArray = $this->toArray();

        // If XML content type, we have to define a parent node name
        if ($contentType === FileParser::CONTENT_TYPE_XML) {
            $actionResultArray['element'] = $actionResultArray;
        }

        // We write the result to the file path
        FileParser::writeToFile($actionResultArray, $filePath, $contentType);
    }
}
