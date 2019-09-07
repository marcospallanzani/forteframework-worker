<?php
/**
 * This file is part of the ForteFramework package.
 *
 * Copyright (c) 2019  Marco Spallanzani <marco@forteframework.com>
 *
 *  For the full copyright and license information,
 *  please view the LICENSE file that was distributed
 *  with this source code.
 */

namespace Forte\Worker\Actions\Checks\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;

/**
 * Class FileExists.
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class FileExists extends AbstractAction
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * FileExists constructor.
     *
     * @param string $filePath The file path to check.
     */
    public function __construct(string $filePath = "")
    {
        parent::__construct();
        $this->filePath = $filePath;
    }

    /**
     * Set the file path for this FileExists instance.
     *
     * @param string $filePath The file path to check
     *
     * @return FileExists
     */
    public function setPath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Return a human-readable string representation of this FileExists instance.
     *
     * @return string A human-readable string representation of this FileExists
     * instance.
     */
    public function stringify(): string
    {
        return "Check if file '" . $this->filePath . "' exists.";
    }

    /**
     * Validate the current action result. This method returns true if the
     * last execution of the apply() method executed correctly (i.e. file
     * exists); false otherwise.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of this FileExists instance.
     *
     * @return bool True if the last execution of the apply() method
     * executed correctly (i.e. file exists); false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        // The ActionResult->result field should be set with a boolean
        // representing the last execution of the apply method.
        return (bool) $actionResult->getResult();
    }

    /**
     * Validate this FileExists instance using its specific validation logic.
     * It returns true if this FileExists instance respects the following rules:
     * - the field 'filePath' must be specified and not empty;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwWorkerException("You must specify the file path.");
        }

        return true;
    }

    /**
     * Run the check.
     *
     * @param ActionResult $actionResult The action result object to register
     * all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields
     * regarding failures and result content.
     *
     * @throws \Exception
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        // We check if the origin file exists
        return $actionResult->setResult($this->fileExists($this->filePath, false));
    }
}
