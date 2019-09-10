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
use Forte\Worker\Actions\NestedActionCallbackInterface;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Helpers\FileParser;

/**
 * Class FileHasValidEntries
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class FileHasValidEntries extends FileExists implements NestedActionCallbackInterface
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
     * @throws ValidationException If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        parent::validateInstance();

        // Check if the given type is supported
        $contentTypeConstants = FileParser::getSupportedContentTypes();
        if (!in_array($this->contentType, $contentTypeConstants)) {
            $this->throwValidationException(
                $this,
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
                /** @var AbstractAction $check */
                $check->isValid();
            } catch (ValidationException $validationException) {
                $wrongChecks[] = $validationException;
            }
        }

        // We check if some nested checks are not valid: if so, we throw an exception
        if ($wrongChecks) {
            $this->throwValidationExceptionWithChildren(
                $this,
                [$wrongChecks],
                "One or more nested actions are not valid."
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

        // We read the file and we convert it to an array, when possible.
        $parsedContent = FileParser::parseFile($this->filePath, $this->contentType);
        if (!is_array($parsedContent)) {
            $this->throwWorkerException(
                "File content of '%s' cannot be converted to an array.",
                $this->filePath
            );
        }

        // We run the modifications configured for this action
        $this->applyWithNestedRunActions(
            $actionResult,
            $this->checks,
            $this,
            $parsedContent
        );

        return $actionResult;
    }

    /**
     * Run the given nested action and modify the given nested action result accordingly.
     *
     * @param AbstractAction $nestedAction The nested action to be run.
     * @param ActionResult $nestedActionResult The nested action result to be modified by
     * the given nested run action.
     * @param array $failedNestedActions A list of failed nested actions.
     * @param mixed $content The content to be used by the run method, if required.
     * @param array $actionOptions Additional options required to run the given
     * AbstractAction subclass instance.
     *
     * @throws ActionException
     */
    public function runNestedAction(
        AbstractAction &$nestedAction,
        ActionResult &$nestedActionResult,
        array &$failedNestedActions,
        &$content = null,
        array &$actionOptions = array()
    ): void
    {
        if ($nestedAction instanceof VerifyArray) {
            $nestedAction->checkContent($content);
        }
        $nestedActionResult = $nestedAction->run();
        if (!$nestedAction->validateResult($nestedActionResult)) {
            $failedNestedActions[] = $nestedActionResult;
        }
    }
}
