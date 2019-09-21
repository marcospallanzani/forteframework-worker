<?php

namespace Forte\Worker\Actions\Lists;

use Forte\Stdlib\DirectoryUtils;
use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\AbstractFileAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\StringHelper;

/**
 * Class FilesInDirectory.
 *
 * Class in charge of running a list of actions on all the files
 * in a specified directory (recursive).
 *
 * @package Forte\Worker\Actions\Lists
 */
class FilesInDirectory extends AbstractAction
{
    /**
     * @var string
     */
    protected $directoryPath;

    /**
     * @var array
     */
    public $filePatterns = [];

    /**
     * @var array
     */
    protected $excludedDirectories = [];

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * FilesInDirectory constructor.
     *
     * @param string $directoryPath The directory to which all configured actions
     * will be applied.
     * @param array $filePatterns A list of file patterns to identify a sub-list
     * of files to be used in the execution of this action. If not specified, all
     * files in the given directory (and its sub-folders) will be used.
     * @param array $excludedDirectories A list of subdirectories to exclude.
     * @param array $actions The actions to be run by this FilesInDirectory instance.
     *
     * @throws ConfigurationException
     */
    public function __construct(
        string $directoryPath = "",
        array $filePatterns = [],
        array $excludedDirectories = [],
        array $actions = []
    ) {
        parent::__construct();
        $this->directoryPath = $directoryPath;
        $this->filePatterns = $filePatterns;
        $this->excludedDirectories = $excludedDirectories;
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
    public function addActions(array $actions): parent
    {
        foreach ($actions as $action) {
            if ($action instanceof AbstractFileAction) {
                $this->addAction($action);
                continue;
            }
            $this->throwConfigurationException(
                $this,
                "Invalid action detected. Found [%s]. AbstractFileAction subclass instance expected.",
                StringHelper::stringifyVariable($action)
            );
        }

        return $this;
    }

    /**
     * Add the given AbstractAction subclass instance to this FilesInDirectory instance.
     *
     * @param AbstractFileAction $action The AbstractFileAction subclass instance
     * to add to this FilesInDirectory instance.
     *
     * @return $this
     */
    public function addAction(AbstractFileAction $action): self
    {
        /**
         * We have to set the path of the given action with a fake one, so that we can
         * by-pass the validation of an empty path. We need to do that, as the file path
         * will be set at run time in the apply method.
         */
        $this->actions[$action->getUniqueExecutionId()] = $this->getActionForBlockExecution(
            $action->path('__EMPTY_FILE_PATH_CHANGED_AT_RUN_TIME__'));

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
     * Set the directory, whose files need to be checked or modified by
     * the configured actions.
     *
     * @param string $directoryPath The directory to which all configured
     * actions will be applied.
     *
     * @return $this
     */
    public function in(string $directoryPath): self
    {
        $this->directoryPath = $directoryPath;

        return $this;
    }

    /**
     * Add a list of directories to exclude from the action execution.
     *
     * @param array $directories A list of directories to exclude from
     * the action execution.
     *
     * @return $this
     */
    public function exclude(array $directories): self
    {
        $this->excludedDirectories = array_merge($this->excludedDirectories, $directories);

        return $this;
    }

    /**
     * Add a list of file patterns to be used in identifying the list of files to
     * be used by the action execution. If no patterns are specified, then all the
     * files in the given directory (and in its sub-folders) will be used.
     *
     * @param array $filePatterns A list of file patterns.
     *
     * @return $this
     */
    public function filePatterns(array $filePatterns): self
    {
        $this->filePatterns = $filePatterns;

        return $this;
    }

    /**
     * Return a human-readable string representation of this FilesInDirectory
     * instance.
     *
     * @return string A human-readable string representation of this
     * FilesInDirectory instance.
     */
    public function stringify(): string
    {
        $message = sprintf(
            "Apply the following actions to all files in '%s' (recursive)",
            $this->directoryPath
        );

        if ($this->excludedDirectories) {
            $message .= sprintf(" (excluded directories: [%s])", implode(', ', $this->excludedDirectories));
        }

        if ($this->filePatterns) {
            $message .= sprintf(" with patterns [%s]", implode(', ', $this->filePatterns));
        }

        $message .= ": " . PHP_EOL;

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
        // We get a list of files to which we will apply the configured actions
        $filesIterator = DirectoryUtils::getFilesList(
            $this->directoryPath,
            $this->filePatterns,
            $this->excludedDirectories
        );

        foreach ($filesIterator as $file) {
            if ($file instanceof \SplFileInfo) {
                foreach ($this->actions as $action) {
                    // We run the current action on the current file
                    /** @var AbstractAction $action */
                    if ($action instanceof AbstractFileAction) {
                        $action->path($file->getPathName());
                    }
                    $caseActionResult = $action->run();
                    $actionResult->addActionResult($caseActionResult);
                }
            }
        }

        // We check if all actions are successful: if so, we set the final result
        // to true; false otherwise.
        if ($actionResult->isSuccessfulAction()) {
            $actionResult->setResult(true);
        } else {
            $actionResult->setResult(false);
        }

        return $actionResult;
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
        if (empty($this->directoryPath)) {
            $this->throwValidationException($this, "Directory path cannot be empty.");
        }

        $wrongActions = [];
        try {
            // We validate each registered AbstractAction subclass instance.
            foreach ($this->actions as $action) {
                if ($action instanceof AbstractFileAction) {
                    $action->isValid();
                } else {
                    $this->throwWorkerException(
                        "Invalid action detected. Found [%s]. AbstractFileAction subclass instance expected.",
                        StringHelper::stringifyVariable($action)
                    );
                }
            }
        } catch (WorkerException $workerException) {
            $wrongActions[] = $workerException;
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
     * Clone and modify the given AbstractAction subclass instance so that it can be executed
     * in a switch case block. It sets the cloned action as FATAL, so that, in case of error,
     * an exception will be thrown and caught in the AbstractAction::run() method. The idea is
     * to stop the execution of a switch-case loop, if an error occurred in the execution of
     * any of its blocks. We also set the cloned action as NON-SUCCESS-REQUIRED, as a negative
     * action result should be accepted as a possible result.
     *
     * @param AbstractAction $blockAction The action to be modified for the execution
     * of a switch case block.
     *
     * @return AbstractAction The modified action to be used in the switch-case blocks.
     */
    protected function getActionForBlockExecution(AbstractAction $blockAction): AbstractAction
    {
        $action = clone $blockAction;
        $action
            ->setIsFatal(true)
            ->setIsSuccessRequired(false)
        ;

        return $action;
    }
}
