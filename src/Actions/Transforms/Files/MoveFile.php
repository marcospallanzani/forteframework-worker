<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractFileAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Factories\WorkerActionFactory;

/**
 * Class MoveFile.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class MoveFile extends AbstractFileAction
{
    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * @var string
     */
    protected $targetPath;

    /**
     * @var bool
     */
    protected $isTargetFullPath = false;

    /**
     * MoveFile constructor.
     *
     * @param string $sourcePath The source file path to be moved.
     * @param string $targetPath The target file path.
     */
    public function __construct(string $sourcePath = "", string $targetPath = "")
    {
        parent::__construct();
        $this->sourcePath       = $sourcePath;
        $this->targetPath       = $targetPath;
        $this->isTargetFullPath = true;
    }

    /**
     * Set the file path to be moved.
     *
     * @param string $sourcePath The source file path to be moved.
     *
     * @return self
     */
    public function move(string $sourcePath): self
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    /**
     * Set the target FULL file path for the previously given
     * source file path. (e.g. /new/folder/new-file-name.php)
     *
     * @param string $targetPath The target path.
     *
     * @return self
     */
    public function to(string $targetPath): self
    {
        $this->targetPath       = $targetPath;
        $this->isTargetFullPath = true;

        return $this;
    }

    /**
     * Set the target directory. The source file name will be
     * kept.
     *
     * @param string $targetDirectory The target directory.
     *
     * @return $this
     */
    public function toFolder(string $targetDirectory): self
    {
        $this->targetPath       = $targetDirectory;
        $this->isTargetFullPath = false;

        return $this;
    }

    /**
     * Set the path required by the MoveFile instance.
     *
     * @param string $path The path to be set.
     *
     * @return $this
     */
    public function path(string $path): MoveFile
    {
        return $this->move($path);
    }

    /**
     * Apply the subclass action.
     *
     * @param ActionResult $actionResult The action result object to
     * register all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated
     * fields regarding failures and result content.
     *
     * @throws \Exception
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        // We check if the origin file exists
        $this->fileExists($this->sourcePath);

//TODO REPLACE THIS CHECK AND TARGET CREATION WITH A IfStatement -> IF TARGET DOES NOT EXIST, CREATE FOLDER
        // We check if the target directory exists
        if ($this->isTargetFullPath) {
            $directory = dirname($this->targetPath);
        } else {
            $directory = $this->targetPath;
        }

        if (!is_dir($directory)) {
            $actionResult->addActionResult(WorkerActionFactory::createMakeDirectory($directory)->run());
        }

        if (!$this->isTargetFullPath) {
            // In this case, we use the same name as the original path
            $this->targetPath .= DIRECTORY_SEPARATOR . basename($this->sourcePath);
        }

        return $actionResult->setResult(
            rename(
                $this->sourcePath,
                $this->targetPath
            )
        );
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
        // The source path cannot be empty
        if (empty($this->sourcePath)) {
            $this->throwValidationException(
                $this, "You must specify a source path."
            );
        }

        // The target path cannot be empty
        if (empty($this->targetPath)) {
            $this->throwValidationException(
                $this, "You must specify a target path."
            );
        }

        return true;
    }

    /**
     * Return a human-readable string representation of this
     * MoveFile instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        $targetFullPath = $this->targetPath;

        // We check if source path is not empty, as the method stringify can be
        // called before the validation methods
        if (!$this->isTargetFullPath && !empty($this->sourcePath)) {
            // In this case, we use the same name as the original path
            $targetFullPath .= DIRECTORY_SEPARATOR . basename($this->sourcePath);
        }

        return sprintf(
            "Move file '%s' to '%s'.",
            $this->sourcePath,
            $targetFullPath
        );
    }
}