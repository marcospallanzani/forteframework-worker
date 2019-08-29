<?php

namespace Forte\Api\Generator\Checkers\Checks;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Filters\Arrays\VerifyArray;
use Forte\Api\Generator\Helpers\FileParser;

/**
 * Class FileHasValidConfigEntries
 *
 * @package Forte\Api\Generator\Checkers\Checks
 */
class FileHasValidConfigEntries extends FileExists
{
    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var array
     */
    protected $checks = array();

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
        parent::isValid();

        // Check if the given type is supported
        try {
            $contentTypeConstants = FileParser::getSupportedContentTypes();

            if (!in_array($this->contentType, $contentTypeConstants)) {
                $this->throwCheckException(
                    $this,
                    "The specified content type '%s' is not supported. Supported types are: '%s'",
                    $this->contentType,
                    implode(',', $contentTypeConstants)
                );
            }

            // Check if the specified checks are well configured
            foreach ($this->checks as $check) {

                if (!$check instanceof VerifyArray) {
                    $this->throwCheckException(
                        $this,
                        "Check parameters should be registered as instances of class '%s'.",
                        VerifyArray::class
                    );
                }

                try {
                    // We check if the current check parameters are valid; if not valid, an exception will be thrown
                    $check->isValid();
                } catch (GeneratorException $generatorException) {
                    $this->throwCheckException($this, $generatorException->getMessage());
                }
            }
        } catch (\ReflectionException $reflectionException) {
            $this->throwCheckException($this,
                "A general error occurred while retrieving the content types list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        return true;
    }

    /**
     * Apply the check.
     *
     * @return bool Returns true if this AbstractCheck subclass
     * instance check has been successfully; false otherwise.
     *
     * @throws CheckException
     * @throws GeneratorException
     */
    public function check(): bool
    {
        // We check if the specified file exists
        $this->checkFileExists($this->filePath);

        $failed = array();

        if ($this->isValid()) {
            // We read the file and we convert it to an array, when possible.
            $parsedContent = FileParser::parseConfigFile($this->filePath, $this->contentType);
            if (!is_array($parsedContent)) {
                $this->throwCheckException(
                    $this,
                    "It was not possible to convert the content of file '%s' to an array.",
                    $this->filePath
                );
            }

            // We check all configured conditions for the configured file
            foreach ($this->checks as $check) {
                try {
                    /** @var VerifyArray $check */
                    if (!$check->checkCondition($parsedContent)) {
                        $failed[] = sprintf("Check failed: %s.", $check);
                    }
                } catch (GeneratorException $e) {
                    $failed[] = sprintf("Check failed: %s. Reason is: %s", $check, $e->getMessage());
                }
            }
        }

        if ($failed) {
            $this->throwCheckException($this, implode(' | ', $failed));
        }

        return true;
    }

    /**
     * Sets the file content type. Accepted values are the FileParser
     * class constants with prefix "CONTENT_TYPE".
     *
     * @param string $type The content type; accepted values are the
     * FileParser class constants with prefix "CONTENT_TYPE".
     *
     * @return FileHasValidConfigEntries
     */
    public function contentType(string $type): self
    {
        $this->contentType = $type;

        return $this;
    }

    /**
     * Checks if the specified decoded file (i.e. converted to array)
     * has the given key.
     *
     * @param string $key The expected key.
     *
     * @return FileHasValidConfigEntries
     */
    public function hasKey(string $key): self
    {
        $this->checks[] = new VerifyArray($key, VerifyArray::CHECK_ANY);

        return $this;
    }

    /**
     * Checks if the specified decoded file (i.e. converted to array)
     * has the given key with an empty value.
     *
     * @param string $key The key with an expected empty value.
     *
     * @return FileHasValidConfigEntries
     */
    public function hasKeyWithEmptyValue(string $key): self
    {
        $this->checks[] = new VerifyArray($key, VerifyArray::CHECK_EMPTY);

        return $this;
    }

    /**
     * Checks if the specified decoded file (i.e. converted to array)
     * has the given key with a non-empty value.
     *
     * @param string $key The key with an expected non-empty value.
     *
     * @return FileHasValidConfigEntries
     */
    public function hasKeyWithNonEmptyValue(string $key): self
    {
        $this->checks[] = new VerifyArray($key, VerifyArray::CHECK_EMPTY, null, true);

        return $this;
    }

    /**
     * Checks if the specified decoded file (i.e. converted to array) has a value,
     * whose key correspond to the given one, and whose value meets the condition
     * defined by the couple value-operation. For more details about the possible
     * conditions, check the class VerifyArray.
     *
     * @param string $key The key.
     * @param mixed $value The expected value
     * @param string $operation The comparison operation to be performed. Accepted
     * values are the VerifyArray constants with prefix "CHECK_".
     *
     * @return FileHasValidConfigEntries
     */
    public function hasKeyWithValue(
        string $key,
        $value,
        string $operation = VerifyArray::CHECK_CONTAINS
    ): self
    {
        $this->checks[] = new VerifyArray($key, $operation, $value);

        return $this;
    }

    /**
     * Returns a string representation of this AbstractCheck subclass instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        $message = "Check if configured keys meet the configured check operations in file '" . $this->filePath . "'.";
        foreach ($this->checks as $key => $check) {
            $message .= " $key. " . (string) $check;
        }

        return $message;
    }
}
