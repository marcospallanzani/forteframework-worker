<?php

namespace Forte\Worker\Transformers\Transforms\File;

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Exceptions\TransformException;
use Forte\Worker\Transformers\Transforms\Arrays\ModifyArray;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Transformers\Transforms\AbstractTransform;

/**
 * Class ChangeFileConfigEntries
 *
 * @package Forte\Worker\Transformers\Transforms\File
 */
class ChangeFileConfigEntries extends AbstractTransform
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
     * ChangeFileConfigEntries constructor.
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
     * Get whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws TransformException
     */
    public function isValid(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwTransformException($this, "You must specify the file path.");
        }

        if (empty($this->contentType)) {
            $this->throwTransformException($this, sprintf(
                "You must specify the content type for file '%s'.",
                $this->filePath
            ));
        }

        // Check if the given type is supported
        try {
            $contentTypeConstants = FileParser::getSupportedContentTypes();

            if (!in_array($this->contentType, $contentTypeConstants)) {
                $this->throwTransformException(
                    $this,
                    "The specified content type '%s' is not supported. Supported types are: '%s'",
                    $this->contentType,
                    implode(',', $contentTypeConstants)
                );
            }

            // Check if the specified modifications are well configured
            foreach ($this->modifications as $modification) {

                if (!$modification instanceof ModifyArray) {
                    $this->throwTransformException(
                        $this,
                        "Modifications should be registered as instances of class '%s'.",
                        ModifyArray::class
                    );
                }

                try {
                    // We check if the current modification is valid; if not valid, an exception will be thrown
                    $modification->isValid();
                } catch (WorkerException $workerException) {
                    $this->throwTransformException($this, $workerException->getMessage());
                }
            }
        } catch (\ReflectionException $reflectionException) {
            $this->throwTransformException($this,
                "A general error occurred while retrieving the content types list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        return true;
    }

    /**
     * Apply the sub-class transformation action.
     *
     * @return bool Returns true if the transform action implemented by
     * this AbstractTransform subclass instance has been successfully
     * applied; false otherwise.
     *
     * @throws WorkerException
     * @throws TransformException
     */
    protected function apply(): bool
    {
        // We check if the specified file exists
        $this->checkFileExists($this->filePath);

        // We read the file and we convert it to an array, when possible.
        $parsedContent = FileParser::parseConfigFile($this->filePath, $this->contentType);
        if (!is_array($parsedContent)) {
            $this->throwTransformException(
                $this,
                "It was not possible to convert the content of file '%s' to an array.",
                $this->filePath
            );
        }

        // We check all configured conditions for the configured file
        $failed = array();
        foreach ($this->modifications as $modification) {
            try {
                /** @var ModifyArray $modification */
                $modification->setModifyContent($parsedContent)->run();
                $parsedContent = $modification->getModifiedContent();
            } catch (WorkerException $e) {
                $failed[] = sprintf("Modification failed: %s. Reason is: %s", $modification, $e->getMessage());
            }
        }

        if ($failed) {
            $this->throwTransformException($this, implode(' | ', $failed));
        }

        // We save the new content to the original file.
        FileParser::writeToConfigFile($parsedContent, $this->filePath, $this->contentType);

        return true;
    }

    /**
     * Sets the file content type. Accepted values are the FileParser class
     * constants with prefix "CONTENT_TYPE".
     *
     * @param string $type The content type; accepted values are the FileParser
     * class constants with prefix "CONTENT_TYPE".
     *
     * @return ChangeFileConfigEntries
     */
    public function contentType(string $type): self
    {
        $this->contentType = $type;

        return $this;
    }

    /**
     * Adds a new modification to this ChangeFileConfigEntries instance,
     * to add the new configuration key with the given value in the specified
     * file. The configuration key can have multiple nested levels, separated
     * by the constant ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     * @param mixed $value The value to be added for the given key.
     *
     * @return ChangeFileConfigEntries
     */
    public function addConfigKeyWithValue(string $key, $value): self
    {
        $this->modifications[] = new ModifyArray($key, ModifyArray::MODIFY_ADD, $value);

        return $this;
    }

    /**
     * Adds a new modification to this ChangeFileConfigEntries instance,
     * to modify the given configuration key with the given value in the
     * specified file. The key can have multiple nested levels, separated
     * by the constant ModifyArray::ARRAY_LEVELS_SEPARATOR. If the given
     * key was not found in the specified file, a new entry will be created.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     * @param mixed $value The value to be added for the given key.
     *
     * @return ChangeFileConfigEntries
     */
    public function modifyConfigKeyWithValue(string $key, $value): self
    {
        $this->modifications[] = new ModifyArray($key, ModifyArray::MODIFY_CHANGE_VALUE, $value);

        return $this;
    }

    /**
     * Adds a new modification to this ChangeFileConfigEntries instance,
     * to remove the given configuration key in the specified file. The
     * key can have multiple nested levels, separated by the constant
     * ModifyArray::ARRAY_LEVELS_SEPARATOR.
     *
     * @param string $key The key to add (nested levels separated by
     * constant ModifyArray::ARRAY_LEVELS_SEPARATOR).
     *
     * @return ChangeFileConfigEntries
     */
    public function removeConfigKey(string $key): self
    {
        $this->modifications[] = new ModifyArray($key, ModifyArray::MODIFY_REMOVE_KEY);

        return $this;
    }

    /**
     * Return a human-readable string representation of this
     * ChangeFileConfigEntries instance.
     *
     * @return string A human-readable string representation
     * of this ChangeFileConfigEntries instance.
     */
    public function stringify(): string
    {
        $message = "Modify the file '" . $this->filePath . "' with the following modifications.";
        foreach ($this->modifications as $key => $modification) {
            $message .= " $key. " . (string) $modification;
        }

        return $message;
    }
}
