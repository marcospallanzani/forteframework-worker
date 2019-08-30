<?php

namespace Forte\Api\Generator\Checkers\Checks;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;

/**
 * Class FileExists.
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
     * @param string $filePath The file path to check.
     */
    public function __construct(string $filePath = "")
    {
        $this->filePath = $filePath;
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if this AbstractCheck subclass instance
     * was correctly configured; false otherwise.
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
     * Run the check.
     *
     * @return bool True if this AbstractCheck subclass instance
     * check was successful; false otherwise.
     *
     * @throws GeneratorException
     */
    protected function check(): bool
    {
        // We check if the origin file exists
        return $this->checkFileExists($this->filePath, false);
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
}