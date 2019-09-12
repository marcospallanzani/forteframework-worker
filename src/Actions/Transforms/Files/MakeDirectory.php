<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;

/**
 * Class MakeDirectory. Class to create a directory (it allows the creation of nested directories).
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class MakeDirectory extends AbstractAction
{
    /**
     * @var string
     */
    protected $directoryPath;

    /**
     * @var string
     */
    protected $mode = 0777;

    /**
     * MakeDirectory constructor.
     *
     * @param string $directoryPath The directory path to create.
     * @param string $mode The creation mode.
     */
    public function __construct(string $directoryPath = "")
    {
        parent::__construct();
        $this->directoryPath = $directoryPath;
    }

    /**
     * Set the directory path to create.
     *
     * @param string $directoryPath The directory path to create.
     *
     * @return MakeDirectory
     */
    public function create(string $directoryPath): self
    {
        $this->directoryPath = $directoryPath;

        return $this;
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
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        return $actionResult->setResult(
            @mkdir(
                $this->directoryPath,
                $this->mode,
                true
            )
        );
    }

    /**
     * Validate this MakeDirectory instance.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The directory path cannot be empty
        if (empty($this->directoryPath)) {
            $this->throwValidationException($this, "You must specify the directory path.");
        }

        return true;
    }

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        return sprintf("Create directory '%s'.", $this->directoryPath);
    }
}
