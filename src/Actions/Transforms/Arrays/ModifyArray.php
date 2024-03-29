<?php

namespace Forte\Worker\Actions\Transforms\Arrays;

use Forte\Stdlib\ArrayUtils;
use Forte\Stdlib\ClassAccessTrait;
use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ThrowErrorsTrait;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\StringHelper;

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
    const MODIFY_ADD_KEY      = "modify_add_key";
    const MODIFY_REMOVE_KEY   = "modify_remove_key";
    const MODIFY_CHANGE_KEY   = "modify_change_key";
    const MODIFY_CHANGE_VALUE = "modify_change_value";

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
    public function __construct(string $key = "", string $action = "", $value = null)
    {
        parent::__construct();
        $this->key    = $key;
        $this->action = $action;
        $this->value  = $value;
    }

    /**
     * Set this ModifyArray instance, so that it adds the given key with the given value
     * to the specified "modify-content".
     *
     * @param string $key The key of the value to be added.
     * @param mixed $value The value to be added with the given key.
     *
     * @return ModifyArray
     */
    public function addKey(string $key, $value): self
    {
        $this->action = self::MODIFY_ADD_KEY;
        $this->key    = $key;
        $this->value  = $value;

        return $this;
    }

    /**
     * Set this ModifyArray instance, so that it modifies the value associated to the
     * given key with the given value in the specified "modify-content". If the key
     * does not exist, it will be added.
     *
     * @param string $key The key of the value to be modified.
     * @param mixed $value The new value for the given key.
     *
     * @return ModifyArray
     */
    public function changeValueByKey(string $key, $value): self
    {
        $this->action = self::MODIFY_CHANGE_VALUE;
        $this->key    = $key;
        $this->value  = $value;

        return $this;
    }

    /**
     * Set this ModifyArray instance, so that it replaces the given old key with the
     * given new key. The new key should be the desired last-level key part of a multi-
     * level key.
     *
     * E.g. to replace key "key2.key3" with "key2.key4" you should pass the following
     * parameters:
     *
     * - $oldKey="key2.key3";
     * - $lastLevelNewKey="key4";
     *
     * NOTE that if the given new index is already set, an exception will be thrown.
     * Let's consider the following array:
     *  [
     *      key1 => value1,
     *      key2 => [
     *          key3 => value3,
     *          key4 => value4,
     *      ]
     *  ]
     *
     * Trying to rename key "key2.key3" to "key2.key4", will throw a WorkerException.
     *
     * @param string $oldKey The key to be replaced by the new given key.
     * @param string $lastLevelNewKey The last-level key to replace the given old key.
     *
     * @return ModifyArray
     */
    public function changeKey(string $oldKey, string $lastLevelNewKey): self
    {
        $this->action = self::MODIFY_CHANGE_KEY;
        $this->key    = $oldKey;
        $this->value  = $lastLevelNewKey;

        return $this;
    }

    /**
     * Set this ModifyArray instance, so that it removes the given key
     * from the specified "modify-content".
     *
     * @param string $key The key to be removed.
     *
     * @return ModifyArray
     */
    public function removeKey(string $key): self
    {
        $this->action = self::MODIFY_REMOVE_KEY;
        $this->key    = $key;

        return $this;
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
    public function modifyContent(array $content): self
    {
        $this->modifyContent = $content;

        return $this;
    }

    /**
     * Validate the given action result. This method returns true if the specified
     * ActionResult instance has a result value that is considered as a positive case
     * by this ModifyArray instance. This happens if the flag 'modificationApplied'
     * is true (i.e. the given array was actually modified) and if the result is a
     * valid array.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked with
     * the specific validation logic of the current ModifyArray instance.
     *
     * @return bool True if the given ActionResult instance has a result value that is
     * considered as a positive case by this ModifyArray instance; false otherwise.
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
     * Return a human-readable string representation of this ModifyArray instance.
     *
     * @return string A human-readable string representation of this ModifyArray
     * instance.
     */
    public function stringify(): string
    {
        switch($this->action) {
            case self::MODIFY_ADD_KEY:
                return sprintf(
                    "Add value '%s' with key '%s'",
                    StringHelper::stringifyVariable($this->value),
                    $this->key
                );
            case self::MODIFY_CHANGE_VALUE:
                return sprintf(
                    "Modify value with key '%s' and set it to '%s'",
                    $this->key,
                    StringHelper::stringifyVariable($this->value)
                );
            case self::MODIFY_REMOVE_KEY:
                return sprintf("Remove key '%s'", $this->key);
            case self::MODIFY_CHANGE_KEY:
                return sprintf(
                    "Change key '%s' and set it to '%s'",
                    $this->key,
                    $this->value
                );
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
            $this->throwValidationException($this, "No key specified.");
        }

        // If no action is specified OR an unsupported action is given, then we
        // throw an error.
        $modifyConstants = $this->getSupportedActions();
        if (!in_array($this->action, $modifyConstants)) {
            $this->throwValidationException(
                $this,
                "Action %s not supported. Supported actions are [%s]",
                $this->action,
                implode(', ', $modifyConstants)
            );
        }

        // We validate the value, which is required only by modify-change-key action.
        // The other actions don't need a value. In the case of remove key, it is just
        // not required; in the case of modify-value and add-key, it should be possible
        // to set an existing key with an empty value or to set a new key with an empty
        // value.
        if ($this->action === self::MODIFY_CHANGE_KEY && empty($this->value)) {
            $this->throwValidationException(
                $this,
                "Action %s requires a value. None or empty value was given.",
                $this->action
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
     * @param array $array The array to be modified.
     * @param string $key The key to be modified, added, removed or whose value
     * needs to be changed.
     * @param string $action The action to perform. Possible values are class
     * constants starting with prefix 'MODIFY_';
     * @param mixed $modifiedValue The value to be used to perform the configured action.
     *
     * @return array|mixed|null
     *
     * @throws WorkerException Error occurred while modifying the array.
     */
    protected function applyChangeToArray(array &$array, string $key, string $action, $modifiedValue): array
    {
        $keysTree = explode(ArrayUtils::ARRAY_KEYS_LEVEL_SEPARATOR, $key, 2);
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
                    if (in_array($action, [self::MODIFY_ADD_KEY, self::MODIFY_CHANGE_VALUE, self::MODIFY_CHANGE_KEY])) {
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
     * @param array $array The array to be modified.
     * @param string $key The key to be modified, added, removed or whose value needs
     * to be changed.
     * @param string $action The action to perform. Possible values are class constants
     * starting with prefix 'MODIFY_';
     * @param mixed $value The value to be used to perform the configured action.
     *
     * @return void
     *
     * @throws WorkerException Error occurred while modifying the array.
     */
    protected function applyChangeByType(array &$array, string $key, string $action, $value): void
    {
        switch($action) {
            case self::MODIFY_ADD_KEY:
            case self::MODIFY_CHANGE_VALUE:
                $this->modificationApplied = true;
                $array[$key] = $value;
                break;
            case self::MODIFY_REMOVE_KEY:
                $this->modificationApplied = true;
                unset($array[$key]);
                break;
            case self::MODIFY_CHANGE_KEY:
                if (array_key_exists($value, $array)) {
                    $this->throwWorkerException(
                        'It is not possible to override an existing key, when using action [%s].',
                        $action
                    );
                }
                $this->modificationApplied = true;
                $oldKeyValue = $array[$key];
                $array[$value] = $oldKeyValue;
                unset($array[$key]);
                break;
        }
    }
}
