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

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Helpers\FileParser;

/**
 * Class FileHasValidConfigEntries
 *
 * @package Forte\Worker\Actions\Checks\Files
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
     * FileHasValidConfigEntries constructor.
     *
     * @param string $filePath The file path to check.
     * @param string $contentType The file content type.
     */
    public function __construct(string $filePath = "", string $contentType = "")
    {
        parent::__construct($filePath);
        $this->contentType = $contentType;
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this FileHasValidConfigEntries
     * instance was well configured; false otherwise.
     *
     * @throws ActionException
     */
    public function isValid(): bool
    {
        parent::isValid();

        // Check if the given type is supported
        $contentTypeConstants = FileParser::getSupportedContentTypes();

        if (!in_array($this->contentType, $contentTypeConstants)) {
            $this->throwActionException(
                $this,
                "The specified content type '%s' is not supported. Supported types are: '%s'",
                $this->contentType,
                implode(',', $contentTypeConstants)
            );
        }

        // Check if the specified checks are well configured
        foreach ($this->checks as $check) {

            if (!$check instanceof VerifyArray) {
                $this->throwActionException(
                    $this,
                    "Check parameters should be registered as instances of class '%s'.",
                    VerifyArray::class
                );
            }

            try {
                // We check if the current check parameters are valid; if not valid, an exception will be thrown
                $check->isValid();
            } catch (WorkerException $workerException) {
                $this->throwActionException($this, $workerException->getMessage());
            }
        }

        return true;
    }

    /**
     * Run the check.
     *
     * @return bool Returns true if this FileHasValidConfigEntries
     * instance check was successful; false otherwise.
     *
     * @throws ActionException
     */
    protected function check(): bool
    {
        // We check if the specified file exists
        $this->checkFileExists($this->filePath);

        $failed = array();

        if ($this->isValid()) {
            // We read the file and we convert it to an array, when possible.
            $parsedContent = FileParser::parseConfigFile($this->filePath, $this->contentType);
            if (!is_array($parsedContent)) {
                $this->throwActionException(
                    $this,
                    "It was not possible to convert the content of file '%s' to an array.",
                    $this->filePath
                );
            }

            // We check all configured conditions for the configured file
            foreach ($this->checks as $check) {
                try {
                    /** @var VerifyArray $check */
                    if (!$check->setCheckContent($parsedContent)->run()) {
                        $failed[] = sprintf("Check failed: %s.", $check);
                    }
                } catch (WorkerException $e) {
                    $failed[] = sprintf("Check failed: %s. Reason is: %s", $check, $e->getMessage());
                }
            }
        }

        if ($failed) {
//TODO WE SHOULD ADD THE FAILED ARRAY TO THE LIST OF NESTED ERROR MESSAGES IN THE ACTIONEXCEPTION CLASS
            $this->throwActionException($this, implode(' | ', $failed));
        }

        return true;
    }

    /**
     * Set the file content type. Accepted values are the FileParser
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
     * Check if the specified decoded file (i.e. converted to array)
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
     * Check if the specified decoded file (i.e. converted to array)
     * has the given key.
     *
     * @param string $key The expected key.
     *
     * @return FileHasValidConfigEntries
     */
    public function doesNotHaveKey(string $key): self
    {
        $this->checks[] = new VerifyArray($key, VerifyArray::CHECK_MISSING_KEY);

        return $this;
    }

    /**
     * Check if the specified decoded file (i.e. converted to array)
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
     * Check if the specified decoded file (i.e. converted to array)
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
     * Check if the specified decoded file (i.e. converted to array) has a value,
     * whose key correspond to the given one, and whose value meets the condition
     * defined by the couple value-action. For more details about the possible
     * conditions, check the class VerifyArray.
     *
     * @param string $key The key.
     * @param mixed $value The expected value
     * @param string $action The comparison action to be performed. Accepted
     * values are the VerifyArray constants with prefix "CHECK_".
     *
     * @return FileHasValidConfigEntries
     */
    public function hasKeyWithValue(
        string $key,
        $value,
        string $action = VerifyArray::CHECK_CONTAINS
    ): self
    {
        $this->checks[] = new VerifyArray($key, $action, $value);

        return $this;
    }

    /**
     * Return a human-readable string representation of this
     * FileHasValidConfigEntries instance.
     *
     * @return string A human-readable string representation
     * of this FileHasValidConfigEntries instance.
     */
    public function stringify(): string
    {
        $message = "Check if configured keys meet the configured check actions in file '" . $this->filePath . "'.";
        foreach ($this->checks as $key => $check) {
            $message .= " $key. " . (string) $check;
        }

        return $message;
    }
}
