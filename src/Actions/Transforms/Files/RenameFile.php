<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;

/**
 * Class RenameFile. This class renames the given source file with
 * the given target file name. This action CANNOT MOVE the given
 * source file to another directory.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class RenameFile extends AbstractAction
{
    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * @var
     */
    protected $targetName;

    /**
     * RenameFile constructor.
     *
     * @param string $sourcePath The source file path to be renamed.
     * @param string $targetName The target file name.
     */
    public function __construct(string $sourcePath = "", string $targetName = "")
    {
        parent::__construct();
        $this->sourcePath = $sourcePath;
        $this->targetName = $targetName;
    }

    /**
     * Set the source file path to be renamed.
     *
     * @param string $sourcePath The source file path to be renamed.
     *
     * @return self
     */
    public function rename(string $sourcePath): self
    {
        $this->sourcePath = $sourcePath;

        return $this;
    }

    /**
     * Set the target file name for the previously given source file path.
     * The target name should NOT be a full path, but just the new desired
     * target name (i.e. without '/' characters). This action only renames
     * the given file and DOES NOT MOVE it to another directory.
     *
     * @param string $targetName The target file name.
     *
     * @return self
     */
    public function to(string $targetName): self
    {
        $this->targetName = trim($targetName, DIRECTORY_SEPARATOR);

        return $this;
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

        return $actionResult->setResult(@rename(
            $this->sourcePath,
            dirname($this->sourcePath) . DIRECTORY_SEPARATOR . $this->targetName
        ));
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
        // The source file path cannot be empty
        if (empty($this->sourcePath)) {
            $this->throwValidationException(
                $this, "You must specify a source path."
            );
        }

        // The target file name cannot be empty
        if (empty($this->targetName)) {
            $this->throwValidationException(
                $this, "You must specify a target name."
            );
        }

        // The target file name should be a relative name (no '/' characters)
        if (strpos($this->targetName, DIRECTORY_SEPARATOR) !== false) {
            $this->throwValidationException(
                $this,
                "Valid target names should not contain any directory " .
                "separator characters. Given value is '%s'.",
                $this->targetName
            );
        }

        return true;
    }

    /**
     * Return a human-readable string representation of this
     * RenameFile instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        return sprintf(
            "Rename file '%s' to '%s'.",
            $this->sourcePath,
            $this->targetName
        );
    }
}
