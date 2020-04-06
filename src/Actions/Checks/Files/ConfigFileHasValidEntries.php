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

use Forte\Stdlib\Exceptions\GeneralException;
use Forte\Stdlib\FileUtils;
use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Actions\NestedActionCallbackInterface;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Exceptions\ValidationException;

/**
 * Class ConfigFileHasValidEntries
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class ConfigFileHasValidEntries extends FileExists implements NestedActionCallbackInterface
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
     * ConfigFileHasValidEntries constructor.
     *
     * @param string $filePath The file path to check.
     * @param string $contentType The file content type.
     *
     * @throws GeneralException Error while setting the content type field.
     */
    public function __construct(
        string $filePath = "",
        string $contentType = ""
    ) {
        parent::__construct();
        /**
         * We MUST set up the content before the path, so that an empty content type
         * could be overridden by a content type guessed from the file extension.
         */
        $this->contentType = $contentType;

        // This action MUST be done after the content type is set.
        $this->path($filePath);
    }

    /**
     * Set the file path for this FileExists instance.
     *
     * @param string $path The file path to check.
     *
     * @return FileExists
     *
     * @throws GeneralException Error while getting file info (extension).
     */
    public function path(string $path): FileExists
    {
        // If the content type is not specified, we try to guess it from the file extension
        if (!empty($path) && empty($this->contentType)) {
            $fileInfo = pathinfo($path);
            $this->contentType = FileUtils::getContentTypeByFileExtension($fileInfo['extension']);
        }

        return parent::path($path);
    }

    /**
     * Set the file content type. Accepted values are the FileUtils
     * class constants with prefix "CONTENT_TYPE".
     *
     * @param string $type The content type; accepted values are the
     * FileUtils class constants with prefix "CONTENT_TYPE".
     *
     * @return ConfigFileHasValidEntries
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
     * @return ConfigFileHasValidEntries
     */
    public function hasKey(string $key): self
    {
        $verifyArray = WorkerActionFactory::createVerifyArray($key, VerifyArray::CHECK_ANY);
        $this->checks[] = $verifyArray;

        return $this;
    }

    /**
     * Check if the specified decoded file (i.e. converted to array)
     * has the given key.
     *
     * @param string $key The expected key.
     *
     * @return ConfigFileHasValidEntries
     */
    public function doesNotHaveKey(string $key): self
    {
        $this->checks[] = WorkerActionFactory::createVerifyArray($key, VerifyArray::CHECK_MISSING_KEY);

        return $this;
    }

    /**
     * Check if the specified decoded file (i.e. converted to array)
     * has the given key with an empty value.
     *
     * @param string $key The key with an expected empty value.
     *
     * @return ConfigFileHasValidEntries
     */
    public function hasKeyWithEmptyValue(string $key): self
    {
        $this->checks[] = WorkerActionFactory::createVerifyArray($key, VerifyArray::CHECK_EMPTY);

        return $this;
    }

    /**
     * Check if the specified decoded file (i.e. converted to array)
     * has the given key with a non-empty value.
     *
     * @param string $key The key with an expected non-empty value.
     *
     * @return ConfigFileHasValidEntries
     */
    public function hasKeyWithNonEmptyValue(string $key): self
    {
        $this->checks[] = WorkerActionFactory::createVerifyArray($key, VerifyArray::CHECK_EMPTY, null, true);

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
     * @param bool $caseSensitive Whether or not a case-sensitive check action
     * should be performed.
     *
     * @return ConfigFileHasValidEntries
     */
    public function hasKeyWithValue(
        string $key,
        $value,
        string $action = VerifyArray::CHECK_CONTAINS,
        bool $caseSensitive = false
    ): self
    {
        $this->checks[] = WorkerActionFactory::createVerifyArray($key, $action, $value)->caseSensitive($caseSensitive);

        return $this;
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

    /**
     * Return a human-readable string representation of this
     * ConfigFileHasValidEntries instance.
     *
     * @return string A human-readable string representation
     * of this ConfigFileHasValidEntries instance.
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
     * @return bool Returns true if this ConfigFileHasValidEntries
     * instance was well configured; false otherwise.
     *
     * @throws ActionException
     */

    /**
     * Validate this ConfigFileHasValidEntries instance using its specific validation logic.
     * It returns true if this ConfigFileHasValidEntries instance respects the following rules:
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
        $contentTypeConstants = FileUtils::getSupportedContentTypes();
        if (!in_array($this->contentType, $contentTypeConstants)) {
            $this->throwValidationException(
                $this,
                "Content type %s not supported. Supported types are [%s].",
                $this->contentType,
                implode(', ', $contentTypeConstants)
            );
        }

        /**
         * We validate the list of nested checks: if they are all valid,
         * true will be returned; otherwise, an exception will be thrown.
         */
        return $this->validateNestedActionsList($this->checks, VerifyArray::class);
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
        $parsedContent = FileUtils::parseFile($this->filePath, $this->contentType);
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
}
