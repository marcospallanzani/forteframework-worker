<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Actions\NestedActionCallbackInterface;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;

/**
 * Class ChangeConfigFileEntries
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class ChangeConfigFileEntries extends AbstractAction implements NestedActionCallbackInterface
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
     * ChangeConfigFileEntries constructor.
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type; accepted values are the FileParser
     * class constants with prefix "CONTENT_TYPE".
     */
    public function __construct(string $filePath = "", string $contentType = "")
    {
        parent::__construct();
        $this->filePath = $filePath;
        $this->contentType = $contentType;
    }

    /**
     *
     * @param string $filePath The file to modify.
     *
     * @return ChangeConfigFileEntries
     */
    public function modify(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Add a nested action to this ChangeConfigFileEntries instance, so that
     * it adds the given value (with the given key) in the specified file.
     * The key can have multiple nested levels, separated by the constant
     * ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     * @param mixed $value The value to be added for the given key.
     *
     * @return ChangeConfigFileEntries
     */
    public function addKeyWithValue(string $key, $value): self
    {
        $this->modifications[] = ActionFactory::createModifyArray($key, ModifyArray::MODIFY_ADD, $value);

        return $this;
    }

    /**
     * Add a nested action to this ChangeConfigFileEntries instance, so that
     * it replaces the value for the given key with the given replace value,
     * in the specified file. The key can have multiple nested levels,
     * separated by the constant ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to modify (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     * @param mixed $value The value to be added for the given key.
     *
     * @return ChangeConfigFileEntries
     */
    public function modifyKeyWithValue(string $key, $value): self
    {
        $this->modifications[] = ActionFactory::createModifyArray($key, ModifyArray::MODIFY_CHANGE_VALUE, $value);

        return $this;
    }

    /**
     * Add a nested action to this ChangeConfigFileEntries instance, so that it
     * removes, from the specified file, the config entries with the given key.
     * The key can have multiple nested levels, separated by the constant
     * ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to remove (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     *
     * @return ChangeConfigFileEntries
     */
    public function removeKey(string $key): self
    {
        $this->modifications[] = ActionFactory::createModifyArray($key, ModifyArray::MODIFY_REMOVE_KEY);

        return $this;
    }

    /**
     * Return a human-readable string representation of this
     * ChangeConfigFileEntries instance.
     *
     * @return string A human-readable string representation
     * of this ChangeConfigFileEntries instance.
     */
    public function stringify(): string
    {
        $message = "Modify the file '" . $this->filePath . "' with the following modifications.";
        foreach ($this->modifications as $key => $modification) {
            if ($modification instanceof AbstractAction) {
                $modificationText = (string) $modification;
            } elseif (is_object($modification)) {
                $modificationText = get_class($modification);
            } else {
                $modificationText = gettype($modification);
            }
            $message .= " $key. " . $modificationText;
        }

        return $message;
    }

    /**
     * Validate this ChangeConfigFileEntries instance using its specific validation logic.
     * It returns true if this ChangeConfigFileEntries instance is well configured, i.e. if:
     * - filePath cannot be an empty string;
     * - content type is supported (FileParser constants starting with prefix 'CONTENT_TYPE');
     * - registered modifications (instances of ModifyArray) are valid;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwValidationException($this, "You must specify the file path.");
        }

        if (empty($this->contentType)) {
            $this->throwValidationException($this, "You must specify the content type.");
        }

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

        /**
         * We validate the list of nested modifications:
         * if they are all valid, true will be returned;
         * otherwise, an exception will be thrown.
         */
        return $this->validateNestedActionsList(
            $this->modifications,
            ModifyArray::class
        );
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

        // We run the modifications configured for this action
        $this->applyWithNestedRunActions(
            $actionResult,
            $this->modifications,
            $this,
            $parsedContent
        );

        // We save the new content to the original file.
        if ($this->validateResult($actionResult)) {
            FileParser::writeToFile($parsedContent, $this->filePath, $this->contentType);
        }

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
        if ($nestedAction instanceof ModifyArray) {
            $nestedAction->modifyContent($content);
        }
        $nestedActionResult = $nestedAction->run();
        if (!$nestedAction->validateResult($nestedActionResult)) {
            $failedNestedActions[] = $nestedActionResult;
        } else {
            $content = $nestedActionResult->getResult();
        }
    }
}