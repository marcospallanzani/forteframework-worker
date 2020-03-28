<?php

namespace Forte\Worker\Runners;

use Forte\Stdlib\Exceptions\GeneralException;
use Forte\Stdlib\FileUtils;
use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;

/**
 * Class AbstractRunner. A base class for all runner implementations.
 *
 * @package Forte\Worker\Runners
 */
class AbstractRunner
{
    /**
     * Actions to apply.
     *
     * @var array An array of AbstractAction subclass instances.
     */
    protected $actions = [];

    /**
     * Action results.
     *
     * @var array An array containing a result set of each run action.
     */
    protected $actionResults = [];

    /**
     * Get a list of actions to be applied.
     *
     * @return array An array of AbstractAction subclass instances.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Get a list of action results.
     *
     * @return array An array of action results.
     */
    public function getActionResults(): array
    {
        return $this->actionResults;
    }

    /**
     * Add an action to apply.
     *
     * @param AbstractAction $action
     */
    public function addAction(AbstractAction $action)
    {
        $this->actions[] = $action;
    }

    /**
     * Apply all configured actions in the given sequence. This method
     * returns a list of ActionResult instances, where each entry
     * represents the result of each run action.
     *
     * @return array A list of ActionResult instances, where each entry
     * represents the result of each run action.
     *
     * @throws ActionException A fatal error happened while executing
     * the actions.
     */
    public function applyActions(): array
    {
        $actionResults = array();
        foreach ($this->actions as $action) {
            if ($action instanceof AbstractAction) {
                // We add the current action result to the global list of action results
                $this->actionResults[$action->getUniqueExecutionId()] = $action->run();
            }
        }
        return $this->actionResults;
    }

    /**
     * Export the configured actions for this AbstractRunner subclass instance to the
     * given destination full file path. If no destination full file path is specified,
     * a default path will be generated.
     *
     * @param string $contentType The report file content type (accepted values are
     * FileUtils constants starting with "CONTENT_TYPE_").
     * @param string $destinationFullFilePath The destination file path. If not given,
     * a default file name will be created.
     * @param bool $includeResults Whether the action result (if available), should be
     * added to the export data.
     *
     * @return string The export full file path.
     *
     * @throws GeneralException Error while exporting the data to the given file path
     * (e.g. given file path is actually a directory).
     */
    public function exportAllActionsToFile(
        string $contentType = FileUtils::CONTENT_TYPE_JSON,
        string $destinationFullFilePath = "",
        bool $includeResults = false
    ): string
    {
        $exportedActions = [];
        foreach ($this->actions as $action) {
            if ($action instanceof AbstractAction) {
                if (array_key_exists($action->getUniqueExecutionId(), $this->actionResults)) {
                    $actionResult = $this->actionResults[$action->getUniqueExecutionId()];
                } else {
                    $actionResult = new ActionResult($action);
                }
                $exportedActions[] = $actionResult->toArray($includeResults);
            }
        }

        return FileUtils::exportArrayReportToFile(
            $exportedActions,
            $contentType,
            $destinationFullFilePath,
            "export_action_"
        );
    }

    /**
     * Reset the current runner instance to its initial state (no configured actions).
     *
     * @return bool True if the runner instance was reset to its initial state.
     */
    public function reset(): bool
    {
        $this->actions       = [];
        $this->actionResults = [];

        return true;
    }

    /**
     * Return true if all the run actions were succesful; false otherwise.
     *
     * @return bool True if all the run actions were succesful; false otherwise.
     */
    public function checkActionResults(): bool
    {
        foreach ($this->actionResults as $result) {
            if ($result instanceof ActionResult && !$result->getAction()->validateResult($result)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Export the given list of ActionResult instances to the destination full file
     * path. If no destination full file path is specified, a default path will be
     * generated.
     *
     * @param array $actionResults A list of ActionResult instances to be exported
     * to the destination full file path.
     * @param string $contentType The report file content type (accepted values are
     * FileUtils constants starting with "CONTENT_TYPE_").
     * @param string $destinationFullFilePath The destination file path. If not given,
     * a default file name will be created.
     *
     * @return string
     *
     * @throws GeneralException Error while exporting the data to the given file path
     * (e.g. given file path is actually a directory).
     */
    public static function exportAllActionResultsToFile(
        array $actionResults,
        string $contentType = FileUtils::CONTENT_TYPE_JSON,
        string $destinationFullFilePath = ""
    ): string
    {
        $exportedActionResults = [];
        foreach ($actionResults as $actionResult) {
            if ($actionResult instanceof ActionResult) {
                $exportedActionResults[] = $actionResult->toArray();
            }
        }

        return FileUtils::exportArrayReportToFile(
            $exportedActionResults,
            $contentType,
            $destinationFullFilePath,
            "export_action_results_"
        );
    }
}
