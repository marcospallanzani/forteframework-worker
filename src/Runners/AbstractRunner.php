<?php

namespace Forte\Worker\Runners;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\FileParser;

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
     * Get a list of actions to be applied.
     *
     * @return array An array of AbstractAction subclass instances.
     */
    public function getActions(): array
    {
        return $this->actions;
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
     * @throws ActionException A critical error was found.
     */
    public function applyActions(): array
    {
        $actionResults = array();
        foreach ($this->actions as $action) {
//TODO DO WE NEED TO CATCH THE EXCEPTIONS HERE? THEY SHOULD BE HANDLED IN THE RUN METHOD.. AFTER ALL, IF FATAL SHOULD THROW AN EXCEPTION
            $actionResult = new ActionResult($action);
            try {
                if ($action instanceof AbstractAction) {
                    $actionResult = $action->run();
                }
            } catch (ActionException $actionException) {

                // If failure is critical (i.e. fatal or success required), we throw it again
                if ($actionException->hasFatalFailures()) {
                    throw $actionException;
                }

                // If not critical, we add the failure to the current result
                $actionResult->addActionFailure($actionException);
            }

            // We add the current action result to the global list of action results
            $actionResults[] = $actionResult;

        }

//TODO CHECK HERE FOR IS SUCCESS REQUIRED

        return $actionResults;
    }

    /**
     * Export the configured actions for this AbstractRunner subclass instance to the
     * given destination full file path. If no destination full file path is specified,
     * a default path will be generated.
     *
     * @param string $contentType The report file content type (accepted values are
     * FileParser constants starting with "CONTENT_TYPE_").
     * @param string $destinationFullFilePath The destination file path. If not given,
     * a default file name will be created.
     *
     * @return string The export full file path.
     *
     * @throws WorkerException
     */
    public function exportAllActionsToFile(
        string $contentType = FileParser::CONTENT_TYPE_JSON,
        string $destinationFullFilePath = ""
    ): string
    {
        $exportedActions = [];
        foreach ($this->actions as $action) {
            if ($action instanceof AbstractAction) {
                $actionResult = new ActionResult($action);
                $exportedActions[] = $actionResult->toArray(false);
            }
        }

        return FileParser::exportArrayReportToFile(
            $exportedActions,
            $contentType,
            $destinationFullFilePath,
            "export_action_"
        );
    }

    /**
     * Export the given list of ActionResult instances to the destination full file
     * path. If no destination full file path is specified, a default path will be
     * generated.
     *
     * @param array $actionResults A list of ActionResult instances to be exported
     * to the destination full file path.
     * @param string $contentType The report file content type (accepted values are
     * FileParser constants starting with "CONTENT_TYPE_").
     * @param string $destinationFullFilePath The destination file path. If not given,
     * a default file name will be created.
     *
     * @return string
     *
     * @throws WorkerException
     */
    public static function exportAllActionResultsToFile(
        array $actionResults,
        string $contentType = FileParser::CONTENT_TYPE_JSON,
        string $destinationFullFilePath = ""
    ): string
    {
        $exportedActionResults = [];
        foreach ($actionResults as $actionResult) {
            if ($actionResult instanceof ActionResult) {
                $exportedActionResults[] = $actionResult->toArray();
            }
        }

        return FileParser::exportArrayReportToFile(
            $exportedActionResults,
            $contentType,
            $destinationFullFilePath,
            "export_action_results_"
        );
    }
}
