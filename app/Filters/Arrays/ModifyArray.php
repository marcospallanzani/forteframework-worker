<?php

namespace Forte\Api\Generator\Filters\Arrays;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Helpers\ClassAccessTrait;
use Forte\Api\Generator\Helpers\ThrowErrorsTrait;

/**
 * Class ModifyArray.
 *
 * @package Forte\Api\Generator\Filters\Arrays
 */
class ModifyArray extends AbstractArray
{
    use ClassAccessTrait, ThrowErrorsTrait;

    /**
     * Supported operations.
     */
    const MODIFY_ADD          = "modify_add";
    const MODIFY_REMOVE_KEY   = "modify_remove_key";
    const MODIFY_CHANGE_VALUE = "modify_change_value";

    /**
     * Elements separator
     */
    const ARRAY_LEVELS_SEPARATOR = ".";

    /**
     * Returns true if this ModifyArray instance is well configured:
     * - key cannot be an empty string;
     * - operation must equal to one of the class constants
     *   starting with prefix 'MODIFY_';
     *
     * @return bool
     *
     * @throws GeneratorException
     */
    public function isValid(): bool
    {
        try {
            $modifyConstants = self::getClassConstants('MODIFY_');
        } catch (\ReflectionException $reflectionException) {
            $this->throwGeneratorException(
                "A general error occurred while retrieving the modifications list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        if (empty($this->key)) {
            $this->throwGeneratorException("You need to specify the 'key' for the following check: '%s'.", $this);
        }

        // If no operation is specified OR an unsupported operation is given, then we throw an error.
        if (!in_array($this->operation, $modifyConstants)) {
            $this->throwGeneratorException(
                "The operation '%s' is not supported. Impacted filter is: '%s'. Supported operations are: '%s'",
                $this->operation,
                $this,
                implode(',', $modifyConstants)
            );
        }

        return true;
    }

    /**
     * Apply the configured change.
     *
     * @param array $array
     *
     * @return array
     *
     * @throws GeneratorException
     */
    public function filter(array $array): array
    {
        if ($this->isValid()) {
            $this->applyChangeToArray($array, $this->key, $this->operation, $this->value);
        }

        return $array;
    }

    /**
     * Applies the configured changes to the given array.
     * This method supports multi-level arrays too.
     *
     * @param array  $array
     * @param string $key
     * @param string $operation
     * @param mixed  $modifiedValue
     *
     * @return array|mixed|null
     */
    public function applyChangeToArray(array &$array, string $key, string $operation, $modifiedValue)
    {
        $keysTree = explode(self::ARRAY_LEVELS_SEPARATOR, $key, 2);
        $value = null;
        if (count($keysTree) <= 2) {
            // We check if a value for the current array key exists;
            // If it does not exist, we throw an error.
            $currentKey = $keysTree[0];
            if (array_key_exists($currentKey, $array)) {
                $value = $array[$currentKey];
            }

            // If a value for the current key was found, we check if we need
            // to iterate again through the given elements tree;
            if (count($keysTree) === 2) {
                if(is_array($value)) {
                    $value = $this->applyChangeToArray(
                        $value,
                        $keysTree[1],
                        $operation,
                        $modifiedValue
                    );
                    // We have to merge the modified sub-array with the parent array
                    $array[$currentKey] = $value;
                } else {
                    // We have found a non-array element but we are not at the end of our keys tree
                    if ($operation === self::MODIFY_ADD || $operation === self::MODIFY_CHANGE_VALUE) {
                        // If key does not exist, we add the missing key
                        // (no need to apply any changes for the remove action)
                        $array[$currentKey] = [];
                        $array[$currentKey] = $this->applyChangeToArray(
                            $array[$currentKey],
                            $keysTree[1],
                            $operation,
                            $modifiedValue
                        );
                    }
                }
            } else {
                // We are at the end of our key tree: we have to modify the current key with the given value
                $this->applyChangeByType(
                    $array,
                    $currentKey,
                    $operation,
                    $modifiedValue
                );
            }

            // We set the modified array as the current value so that it will be added
            // to the parent array in previous calls to this method
            $value = $array;
        }
        return $value;
    }

    /**
     * Applies the given operation to the given array for the given key and value.
     *
     * @param array  $array
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     *
     * @return void
     */
    public function applyChangeByType(array &$array, string $key, string $operation, $value): void
    {
        switch($operation) {
            case self::MODIFY_ADD:
            case self::MODIFY_CHANGE_VALUE:
                $array[$key] = $value;
                break;
            case self::MODIFY_REMOVE_KEY:
                unset($array[$key]);
                break;
        }
    }

    /**
     * Returns a human-readable description of this check operation.
     *
     * @return string
     */
    public function getOperationMessage(): string
    {
        switch($this->operation) {
            case self::MODIFY_ADD:
                return sprintf("Add value '%s' with key '%s'", $this->stringifyValue(), $this->key);
            case self::MODIFY_CHANGE_VALUE:
                return sprintf("Modify key '%s' and set it to '%s'", $this->key, $this->stringifyValue());
            case self::MODIFY_REMOVE_KEY:
                return sprintf("Remove key '%s'", $this->key);
            default:
                return "";
        }
    }
}
