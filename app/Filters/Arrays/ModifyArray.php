<?php

namespace Forte\Worker\Filters\Arrays;

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class ModifyArray.
 *
 * @package Forte\Worker\Filters\Arrays
 */
class ModifyArray extends AbstractArray
{
    use ClassAccessTrait, ThrowErrorsTrait;

    /**
     * Supported actions.
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
     * - action must equal to one of the class constants
     *   starting with prefix 'MODIFY_';
     *
     * @return bool
     *
     * @throws WorkerException
     */
    public function isValid(): bool
    {
        if (empty($this->key)) {
            $this->throwGeneratorException("You need to specify the 'key' for the following check: '%s'.", $this);
        }

        // If no action is specified OR an unsupported action is given, then we throw an error.
        $modifyConstants = $this->getSupportedActions();
        if (!in_array($this->action, $modifyConstants)) {
            $this->throwGeneratorException(
                "The action '%s' is not supported. Impacted filter is: '%s'. Supported actions are: '%s'",
                $this->action,
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
     * @throws WorkerException
     */
    public function filter(array $array): array
    {
        if ($this->isValid()) {
            $this->applyChangeToArray($array, $this->key, $this->action, $this->value);
        }

        return $array;
    }

    /**
     * Applies the configured changes to the given array.
     * This method supports multi-level arrays too.
     *
     * @param array  $array
     * @param string $key
     * @param string $action
     * @param mixed  $modifiedValue
     *
     * @return array|mixed|null
     */
    public function applyChangeToArray(array &$array, string $key, string $action, $modifiedValue)
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
                        $action,
                        $modifiedValue
                    );
                    // We have to merge the modified sub-array with the parent array
                    $array[$currentKey] = $value;
                } else {
                    // We have found a non-array element but we are not at the end of our keys tree
                    if ($action === self::MODIFY_ADD || $action === self::MODIFY_CHANGE_VALUE) {
                        // If key does not exist, we add the missing key
                        // (no need to apply any changes for the remove action)
                        $array[$currentKey] = [];
                        $array[$currentKey] = $this->applyChangeToArray(
                            $array[$currentKey],
                            $keysTree[1],
                            $action,
                            $modifiedValue
                        );
                    }
                }
            } else {
                // We are at the end of our key tree: we have to modify the current key with the given value
                $this->applyChangeByType(
                    $array,
                    $currentKey,
                    $action,
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
     * Applies the given action to the given array for the given key and value.
     *
     * @param array  $array
     * @param string $key
     * @param string $action
     * @param mixed  $value
     *
     * @return void
     */
    public function applyChangeByType(array &$array, string $key, string $action, $value): void
    {
        switch($action) {
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
     * Returns a human-readable description of this check action.
     *
     * @return string
     */
    public function getActionMessage(): string
    {
        switch($this->action) {
            case self::MODIFY_ADD:
                return sprintf("Add value '%s' with key '%s'", $this->stringifyValue(), $this->key);
            case self::MODIFY_CHANGE_VALUE:
                return sprintf("Modify key '%s' and set it to '%s'", $this->key, $this->stringifyValue());
            case self::MODIFY_REMOVE_KEY:
                return sprintf("Remove key '%s'", $this->key);
            default:
                return "Unsupported action";
        }
    }

    /**
     * Return a list of all available actions.
     *
     * @return array
     *
     * @throws WorkerException
     */
    public function getSupportedActions(): array
    {
        try {
            return self::getClassConstants('MODIFY_');
        } catch (\ReflectionException $reflectionException) {
            $this->throwGeneratorException(
                "An error occurred while retrieving the list of supported actions. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }
    }
}
