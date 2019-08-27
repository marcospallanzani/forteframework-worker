<?php

namespace Forte\Api\Generator\Checkers\Checks;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;

/**
 * Class FileExists
 *
 * @package Forte\Api\Generator\Checkers\Checks
 */
class FileExists extends AbstractCheck
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * FileExists constructor.
     *
     * @param string $filePath The file path to check
     */
    public function __construct(string $filePath = "")
    {
        $this->filePath = $filePath;
    }

    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this AbstractCheck subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws CheckException
     */
    public function isValid(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwCheckException($this, "You must specify the file path.");
        }

        return true;
    }

    /**
     * Apply the check.
     *
     * @return bool Returns true if this AbstractCheck subclass
     * instance check has been successfully; false otherwise.
     *
     * @throws GeneratorException
     */
    public function check(): bool
    {
        // We check if the origin file exists
        return $this->checkFileExists($this->filePath, false);
    }

    /**
     * Sets the file path for this FileExists instance.
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
     * Returns a string representation of this AbstractCheck subclass instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return "Check if file '" . $this->filePath . "' exists.";
    }
}