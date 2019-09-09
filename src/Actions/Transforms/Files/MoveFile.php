<?php


namespace Forte\Worker\Actions\Transforms\Files;


use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;

/**
 * Class MoveFile.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class MoveFile extends AbstractAction
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
     * MoveFile constructor.
     *
     * @param string $sourcePath The source file path to be moved.
     * @param string $targetPath The target file path.
     */
    public function __construct(string $sourcePath = "", string $targetPath = "")
    {
        parent::__construct();
        $this->sourcePath = $sourcePath;
        $this->targetPath = $targetPath;
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
     * Set the target file path for the previously given source file path.
     *
     * @param string $targetPath The target path.
     *
     * @return self
     */
    public function to(string $targetPath): self
    {
        $this->targetPath = $targetPath;

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
        return sprintf(
            "Move file '%s' to '%s'.",
            $this->sourcePath,
            $this->targetPath
        );
    }
}