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
use Forte\Worker\Exceptions\ValidationException;

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
    public function path(string $filePath): self
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
     * Validate this FileExists instance using its specific validation logic.
     * It returns true if this FileExists instance respects the following rules:
     * - the field 'filePath' must be specified and not empty;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws ValidationException If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwValidationException($this, "You must specify the file path.");
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
