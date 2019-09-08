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

use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Helpers\FileParser;

/**
 * Class FileHasValidEntries
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class FileHasValidEntries extends FileExists
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
     * FileHasValidEntries constructor.
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
     * Set the file content type. Accepted values are the FileParser
     * class constants with prefix "CONTENT_TYPE".
     *
     * @param string $type The content type; accepted values are the
     * FileParser class constants with prefix "CONTENT_TYPE".
     *
     * @return FileHasValidEntries
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
     * @return FileHasValidEntries
     */
    public function hasKey(string $key): self
    {
        $verifyArray = new VerifyArray($key, VerifyArray::CHECK_ANY);
        $this->checks[] = $verifyArray;

        return $this;
    }

    /**
     * Check if the specified decoded file (i.e. converted to array)
     * has the given key.
     *
     * @param string $key The expected key.
     *
     * @return FileHasValidEntries
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
     * @return FileHasValidEntries
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
     * @return FileHasValidEntries
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
     * @return FileHasValidEntries
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
     * FileHasValidEntries instance.
     *
     * @return string A human-readable string representation
     * of this FileHasValidEntries instance.
     */
    public function stringify(): string
    {
        $message = "Run the following checks in file '" . $this->filePath . "':";
        foreach ($this->checks as $key => $check) {
            $message .= " $key. " . (string) $check;
        }

        return $message;
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this FileHasValidEntries
     * instance was well configured; false otherwise.
     *
     * @throws ActionException
     */

    /**
     * Validate this FileHasValidEntries instance using its specific validation logic.
     * It returns true if this FileHasValidEntries instance respects the following rules:
     * - the field 'filePath' must be specified and not empty;
     * - the field 'contentType' is not empty and has an accepted value;
     * - the configured checks are in a valid state too;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        parent::validateInstance();

        // Check if the given type is supported
        $contentTypeConstants = FileParser::getSupportedContentTypes();
        if (!in_array($this->contentType, $contentTypeConstants)) {
            $this->throwWorkerException(
                "Content type %s not supported. Supported types are [%s].",
                $this->contentType,
                implode(', ', $contentTypeConstants)
            );
        }

        // Check if the specified checks are well configured
        $wrongChecks = array();
        foreach ($this->checks as $check) {
            // We validate all the nested checks
            try {
                $check->isValid();
            } catch (ActionException $actionException) {
                $wrongChecks[] = $actionException;
            }
        }

        // We check if some nested checks are not valid: if so, we throw an exception
        if ($wrongChecks) {
            $this->throwActionExceptionWithChildren(
                $this,
                [$wrongChecks],
                "One or more nested checks are not valid."
            );
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
        // We check if the specified file exists
        $this->fileExists($this->filePath);

        $failedNestedActions = array();

        // We read the file and we convert it to an array, when possible.
        $parsedContent = FileParser::parseFile($this->filePath, $this->contentType);
        if (!is_array($parsedContent)) {
            $this->throwWorkerException(
                "File content of '%s' cannot be converted to an array.",
                $this->filePath
            );
        }

        // We check all configured conditions for the configured file
        foreach ($this->checks as $check) {
            // We create the action result object for the current check
            $checkResult = new ActionResult($check);
            try {
                /** @var VerifyArray $check */
                $checkResult = $check->setCheckContent($parsedContent)->run();
                if (!$check->validateResult($checkResult)) {
                    $failedNestedActions[] = $checkResult;
                }
            } catch (ActionException $actionException) {
                // We handle the caught ActionException for the just-run failed check: if critical,
                // we throw it again so that it can be caught and handled in the run method
                if ($check->isFatal() || $check->isSuccessRequired()) {
                    throw $actionException;
                }

                /**
                 * If we get to this point, it means that the just-checked failed
                 * check is NOT FATAL; in this case, we add to the list of failed
                 * checks and we continue the execution of the current foreach loop.
                 */
                $checkResult->addActionFailure($actionException);
                $failedNestedActions[] = $checkResult;
            }
        }

        $globalResult = true;

        if ($failedNestedActions) {

            // We generate a failure instance to handle the fatal/success-required cases
            $actionException = $this->getActionException($actionResult->getAction(), "One or more sub-checks failed.");
            foreach ($failedNestedActions as $failedNestedAction) {
                foreach ($failedNestedAction->getActionFailures() as $failure) {
                    $actionException->addChildFailure($failure);
                }
            }

            // If fatal or success-required, we throw the exception
            if ($this->isFatal() || $this->isSuccessRequired()) {
                throw $actionException;
            }

            // If not fatal, we add the current failure to the list of failures for this action
            $actionResult->addActionFailure($actionException);

            $globalResult = false;
        }

        // The action executed without failures
        return $actionResult->setResult($globalResult);
    }
}
