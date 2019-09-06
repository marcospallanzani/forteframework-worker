<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;

/**
 * Class ChangeFileEntries
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class ChangeFileEntries extends AbstractAction
{
    /**
     * The file to modify.
     *
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var array
     */
    protected $modifications = array();

    /**
     * ChangeFileEntries constructor.
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values:
     * constants FileParser::CONTENT_TYPE_XXX).
     */
    public function __construct(string $filePath, string $contentType)
    {
        $this->filePath = $filePath;
        $this->contentType = $contentType;
    }

    /**
     * Sets the file content type. Accepted values are the FileParser class
     * constants with prefix "CONTENT_TYPE".
     *
     * @param string $type The content type; accepted values are the FileParser
     * class constants with prefix "CONTENT_TYPE".
     *
     * @return ChangeFileEntries
     */
    public function contentType(string $type): self
    {
        $this->contentType = $type;

        return $this;
    }

    /**
     * Adds a new modification to this ChangeFileEntries instance,
     * to add the new pair key-value in the specified file. The
     * key can have multiple nested levels, separated by the constant
     * ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     * @param mixed $value The value to be added for the given key.
     *
     * @return ChangeFileEntries
     */
    public function addKeyWithValue(string $key, $value): self
    {
        $this->modifications[] = new ModifyArray($key, ModifyArray::MODIFY_ADD, $value);

        return $this;
    }

    /**
     * Adds a new modification to this ChangeFileEntries instance,
     * to modify the given pair key-value in the specified file.
     * The key can have multiple nested levels, separated by the constant
     * ModifyArray::ARRAY_LEVELS_SEPARATOR. If the given key was not found
     * in the specified file, a new entry will be created.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     * @param mixed $value The value to be added for the given key.
     *
     * @return ChangeFileEntries
     */
    public function modifyKeyWithValue(string $key, $value): self
    {
        $this->modifications[] = new ModifyArray($key, ModifyArray::MODIFY_CHANGE_VALUE, $value);

        return $this;
    }

    /**
     * Adds a new modification to this ChangeFileEntries instance, to remove
     * the given key in the specified file. The key can have multiple nested
     * levels, separated by the constant ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     *
     * @return ChangeFileEntries
     */
    public function removeKey(string $key): self
    {
        $this->modifications[] = new ModifyArray($key, ModifyArray::MODIFY_REMOVE_KEY);

        return $this;
    }

    /**
     * Validate the given action result. This method returns true if the
     * given ActionResult instance has a result value that is considered
     * as a positive case by this AbstractAction subclass instance.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of the current AbstractAction subclass
     * instance.
     *
     * @return bool True if the given ActionResult instance has a result value
     * that is considered as a positive case by this AbstractAction subclass
     * instance; false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        // The ActionResult->result field should be set with a boolean
        // representing the last execution of the apply method.
        return (bool) $actionResult->getResult();
    }

    /**
     * Return a human-readable string representation of this
     * ChangeFileEntries instance.
     *
     * @return string A human-readable string representation
     * of this ChangeFileEntries instance.
     */
    public function stringify(): string
    {
        $message = "Modify the file '" . $this->filePath . "' with the following modifications.";
        foreach ($this->modifications as $key => $modification) {
            $message .= " $key. " . (string) $modification;
        }

        return $message;
    }

    /**
     * Validate this ChangeFileEntries instance using its specific validation logic.
     * It returns true if this ChangeFileEntries instance is well configured, i.e. if:
     * - filePath cannot be an empty string;
     * - content type is supported (FileParser constants starting with prefix 'CONTENT_TYPE');
     * - registered modifications (instances of ModifyArray) are valid;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwActionException($this, "You must specify the file path.");
        }

        if (empty($this->contentType)) {
            $this->throwActionException($this, "You must specify the content type.");
        }

        // Check if the given type is supported
        $contentTypeConstants = FileParser::getSupportedContentTypes();
        if (!in_array($this->contentType, $contentTypeConstants)) {
            $this->throwActionException(
                $this,
                "Content type %s not supported. Supported types are [%s].",
                $this->contentType,
                implode(', ', $contentTypeConstants)
            );
        }

        // Check if the specified modifications are well configured
        foreach ($this->modifications as $modification) {

            if (!$modification instanceof ModifyArray) {
                $this->throwActionException(
                    $this,
                    "Modifications should be registered as instances of class %s.",
                    ModifyArray::class
                );
            }

            try {
                // We check if the current modification is valid; if not valid,
                // an ActionException should be thrown
                $modification->isValid();
            } catch (ActionException $actionException) {
                $this->throwActionException(
                    $this,
                    "Invalid modification: %s. Error message: %s",
                    $actionException->getAction(),
                    $actionException->getMessage()
                );
            }
        }

        return true;
    }

    /**
     * Apply the configured modifications in the specified file.
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
                "Impossible to convert the content of file '%s' to an array.",
                $this->filePath
            );
        }

        // We check all configured conditions for the configured file
        $failedNestedActions = array();
        foreach ($this->modifications as $modification) {
            // We create the action result object for the current check
            $modificationResult = new ActionResult($modification);
            try {
                /** @var ModifyArray $modification */
                $modificationResult = $modification->setModifyContent($parsedContent)->run();
                if (!$modification->validateResult($modificationResult)) {
                    $failedNestedActions[] = $modificationResult;
                } else {
                    $parsedContent = $modificationResult->getResult();
                }
            } catch (ActionException $actionException) {
                // We handle the caught ActionException for the just-run failed check: if critical,
                // we throw it again so that it can be caught and handled in the run method
                if ($modification->isFatal() || $modification->isSuccessRequired()) {
                    throw $actionException;
                }

                /**
                 * If we get to this point, it means that the just-checked failed
                 * check is NOT FATAL; in this case, we add to the list of failed
                 * checks and we continue the execution of the current foreach loop.
                 */
                $modificationResult->addActionFailure($actionException);
                $failedNestedActions[] = $modificationResult;
            }
        }

        /**
         * We check the results of the nested modification actions:
         * - if no error, we can save the new content.
         * If some errors occurred, we have to check if they are critical or not:
         * - if critical, we throw a global exception;
         * - if not critical, we save the partially modified content and we return.
         */
        $globalResult = true;
        if ($failedNestedActions) {

            // We generate a failure instance to handle the fatal/success-required cases
            $actionException = $this->getActionException($actionResult->getAction(), "One or more sub-modifications failed.");
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

        // We save the new content to the original file.
        FileParser::writeToFile($parsedContent, $this->filePath, $this->contentType);

        // The action executed without failures
        return $actionResult->setResult($globalResult);
    }
}
