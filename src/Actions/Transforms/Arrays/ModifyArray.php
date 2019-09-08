<?php

namespace Forte\Worker\Actions\Transforms\Arrays;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class ModifyArray.
 *
 * @package Forte\Worker\Actions\Transforms\Arrays
 */
class ModifyArray extends AbstractAction
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
     * @var string
     */
    protected $key;

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var bool
     */
    protected $modificationApplied = false;

    /**
     * The content to be modified.
     *
     * @var array
     */
    protected $modifyContent = [];

    /**
     * AbstractArray constructor.
     *
     * @param string $key The array key to access (multi-level keys separated by '.').
     * @param string $action The action to perform (look inside isValid() implementation
     * for list of supported values).
     * @param mixed  $value The value to set/change/remove.
     */
    public function __construct(string $key, string $action, $value = null)
    {
        parent::__construct();
        $this->key    = $key;
        $this->action = $action;
        $this->value  = $value;
    }

    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the action.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Set the content to be checked. This method is useful to update a ModifyArray
     * instance with a new content, to apply the configured condition against the
     * new content.
     *
     * @param array $content The content to be checked.
     *
     * @return ModifyArray
     */
    public function setModifyContent(array $content): self
    {
        $this->modifyContent = $content;

        return $this;
    }

    /**
     * Returned the modified array.
     *
     * @return array
     */
    public function getModifiedContent(): array
    {
        return $this->modifyContent;
    }

    /**
     * Validate the given action result. This method returns true if the
     * given ActionResult instance has a result value that is considered
     * as a positive case by this ModifyArray instance. This happens if
     * the flag 'modificationApplied' is true (i.e. the given array was
     * actually modified) and if the result is a valid array.
     *
     * @param ActionResult $actionResult The ActionResult instance to
     * be checked with the specific validation logic of the current
     * ModifyArray instance.
     *
     * @return bool True if the given ActionResult instance has a result
     * value that is considered as a positive case by this ModifyArray
     * instance; false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        return ($this->modificationApplied && is_array($actionResult->getResult()));
    }

    /**
     * Return a list of all available actions.
     *
     * @return array
     */
    public function getSupportedActions(): array
    {
        return self::getClassConstants('MODIFY_');
    }

    /**
     * Return a human-readable string representation of this
     * ModifyArray instance.
     *
     * @return string A human-readable string representation
     * of this ModifyArray instance.
     */
    public function stringify(): string
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
     * Returns a string representation of this ModifyArray instance.
     *
     * @return false|string
     */
    public function __toString()
    {
        return $this->stringify();
    }

    /**
     * Validate this ModifyArray instance using its specific validation logic.
     * It returns true if this ModifyArray instance is well configured, i.e. if:
     * - key cannot be an empty string;
     * - action must equal to one of the class constants
     *   starting with prefix 'MODIFY_';
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        if (empty($this->key)) {
            $this->throwActionException($this, "No key specified.");
        }

        // If no action is specified OR an unsupported action is given, then we throw an error.
        $modifyConstants = $this->getSupportedActions();
        if (!in_array($this->action, $modifyConstants)) {
            $this->throwActionException(
                $this,
                "Action %s not supported. Supported actions are [%s]",
                $this->action,
                implode(', ', $modifyConstants)
            );
        }

        return true;
    }

    /**
     * Apply the configured modifications to the given array (modifyContent field).
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
        $this->applyChangeToArray($this->modifyContent, $this->key, $this->action, $this->value);
        return $actionResult->setResult($this->modifyContent);
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
    protected function applyChangeToArray(array &$array, string $key, string $action, $modifiedValue): array
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
    protected function applyChangeByType(array &$array, string $key, string $action, $value): void
    {
        switch($action) {
            case self::MODIFY_ADD:
            case self::MODIFY_CHANGE_VALUE:
                $this->modificationApplied = true;
                $array[$key] = $value;
                break;
            case self::MODIFY_REMOVE_KEY:
                $this->modificationApplied = true;
                unset($array[$key]);
                break;
        }
    }

    /**
     * Returns a string version of the set value (it converts arrays to json).
     *
     * @return string
     */
    protected function stringifyValue(): string
    {
        if (is_array($this->value)) {
            return json_encode($this->value);
        }
        return (string) $this->value;
    }
}
