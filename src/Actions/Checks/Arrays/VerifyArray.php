<?php

namespace Forte\Worker\Actions\Checks\Arrays;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\MissingKeyException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Helpers\StringParser;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class VerifyArray. Class used to wrap all required check parameters.
 *
 * @package Forte\Worker\Actions\Checks
 */
class VerifyArray extends AbstractAction
{
    use ClassAccessTrait, ThrowErrorsTrait;

    /**
     * Supported actions.
     */
    const CHECK_STARTS_WITH  = "check_starts_with";
    const CHECK_ENDS_WITH    = "check_ends_with";
    const CHECK_CONTAINS     = "check_contains";
    const CHECK_EQUALS       = "check_equals";
    const CHECK_EMPTY        = "check_empty";

//TODO CONSIDER REMOVING ANY -> REVERSE OF EMPTY
    const CHECK_ANY          = "check_any";

//TODO CONSIDER CONVERTING TO HAS_KEY
    const CHECK_MISSING_KEY  = "check_missing_key";

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
     * If true, the opposite check will be performed
     * (e.g. contains -> not-contains).
     *
     * @var bool
     */
    protected $reverseAction = false;

    /**
     * The content to be checked.
     *
     * @var array
     */
    protected $checkContent = [];

    /**
     * VerifyArray constructor.
     *
     * @param string $key The array key to access (multi-level keys separated by '.').
     * @param string $action The operation to perform (look inside isValid() implementation
     * for list of supported values).
     * @param mixed $value The value to be matched by the action condition.
     * @param bool $reverseAction Whether the reverse actions should be performed or not
     * (e.g. contains -> not-contains).
     */
    public function __construct(
        string $key = "",
        string $action = "",
        $value = null,
        bool $reverseAction = false
    ) {
        parent::__construct();
        $this->key           = $key;
        $this->action        = $action;
        $this->value         = $value;
        $this->reverseAction = $reverseAction;
    }

    /**
     * Set this VerifyArray instance, so that it checks if the checked content has a key,
     * whose value starts with the given value.
     *
     * @param string $key The array key to be checked (multi-level keys separated by '.').
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyArray
     */
    public function startsWith(string $key, string $value): self
    {
        $this->action = self::CHECK_STARTS_WITH;
        $this->value  = $value;
        $this->key    = $key;

        return $this;
    }

    /**
     * Set this VerifyArray instance, so that it checks if the checked content has a key,
     * whose value ends with the given value.
     *
     * @param string $key The array key to be checked (multi-level keys separated by '.').
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyArray
     */
    public function endsWith(string $key, string $value): self
    {
        $this->action = self::CHECK_ENDS_WITH;
        $this->value  = $value;
        $this->key    = $key;

        return $this;
    }

    /**
     * Set this VerifyArray instance, so that it checks if the checked content has a key,
     * whose value contains the given value.
     *
     * @param string $key The array key to be checked (multi-level keys separated by '.').
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyArray
     */
    public function contains(string $key, string $value): self
    {
        $this->action = self::CHECK_CONTAINS;
        $this->value  = $value;
        $this->key    = $key;

        return $this;
    }

    /**
     * Set this VerifyArray instance, so that it checks if the checked content has a key,
     * whose value is equal to the given value.
     *
     * @param string $key The array key to be checked (multi-level keys separated by '.').
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyArray
     */
    public function isEqualTo(string $key, string $value): self
    {
        $this->action = self::CHECK_EQUALS;
        $this->value  = $value;
        $this->key    = $key;

        return $this;
    }

    /**
     * Set this VerifyArray instance, so that it checks if the checked content has a key,
     * whose value is empty.
     *
     * @param string $key The array key to be checked (multi-level keys separated by '.').
     *
     * @return VerifyArray
     */
    public function isEmpty(string $key): self
    {
        $this->action = self::CHECK_EMPTY;
        $this->key    = $key;

        return $this;
    }

    /**
     * Set this VerifyArray instance, so that it checks if the checked content has an entry
     * with the given key,
     *
     * @param string $key The array key to be checked (multi-level keys separated by '.').
     *
     * @return VerifyArray
     */
    public function isKeyMissing(string $key): self
    {
        $this->action = self::CHECK_MISSING_KEY;
        $this->key    = $key;

        return $this;
    }

    /**
     * Reverse the current configured action to its opposite.
     *
     * e.g.
     * contains -> does not contain
     * starts with -> does not start with
     * ends with -> does not end with
     * is empty -> is not empty
     * is key missing -> has key
     *
     * @return VerifyArray
     */
    public function reverse(): self
    {
        $this->reverseAction = !$this->reverseAction;

        return $this;
    }

    /**
     * Set the content to be checked. This method is useful to update a VerifyArray
     * instance with a new content, to verify the configured condition against the
     * new content.
     *
     * @param array $content The content to be checked.
     *
     * @return VerifyArray
     */
    public function checkContent(array $content): self
    {
        $this->checkContent = $content;

        return $this;
    }

    /**
     * Return a list of all available actions.
     *
     * @return array
     */
    public function getSupportedActions(): array
    {
        return self::getClassConstants('CHECK_');
    }

    /**
     * Return a human-readable string representation of this
     * VerifyArray instance.
     *
     * @return string A human-readable string representation
     * of this VerifyArray instance.
     */
    public function stringify(): string
    {
        $baseMessage = "Check if key '" . $this->key . "' is set";
        $reverseAction = $this->getReverseActionTag();
        switch($this->action) {
            case self::CHECK_ANY:
                return "$baseMessage and has any value";
            case self::CHECK_CONTAINS:
                return sprintf(
                    "%s and %s value '%s'",
                    $baseMessage,
                    $reverseAction,
                    StringParser::stringifyVariable($this->value)
                );
            case self::CHECK_ENDS_WITH:
            case self::CHECK_STARTS_WITH:
                return sprintf(
                    "%s and %s with value '%s'",
                    $baseMessage,
                    $reverseAction,
                    StringParser::stringifyVariable($this->value)
                );
            case self::CHECK_EQUALS:
                return sprintf(
                    "%s and %s equal to value '%s'",
                    $baseMessage,
                    $reverseAction,
                    StringParser::stringifyVariable($this->value)
                );
            case self::CHECK_EMPTY:
                return "$baseMessage and $reverseAction empty (empty string or null)";
            case self::CHECK_MISSING_KEY:
                return "Check if key '" . $this->key . "' $reverseAction set";
            default:
                return $baseMessage;
        }
    }

    /**
     * Returns a string representation of this VerifyArray instance.
     *
     * @return false|string
     */
    public function __toString()
    {
        return $this->stringify();
    }

    /**
     * Validate this VerifyArray instance using its specific validation logic.
     * It returns true if this VerifyArray instance respects the following rules:
     * - the field 'key' must be specified and not empty;
     * - the field 'action' must be specified and not empty;
     * - if an empty 'action' value is specified (e.g. null, ""), the valid actions
     *   are 'check_equals', 'check_empty' or 'check_any';
     *
     * The check `check_any` with an empty value can be used to check if a key is set.
     * The check `check_empty` with an empty value can be used to check if a key is set
     * and its value is empty or null.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // Validate the key
        if (empty($this->key)) {
            $this->throwValidationException($this, "You must specify the key to verify.");
        }

        // Validate the action.
        if (empty($this->action)) {
            $this->throwValidationException($this, "You must specify the action type.");
        }

        // If no action is specified OR an unsupported action is given, then we throw an error.
        $checkActionsConstants = $this->getSupportedActions();
        if (!in_array($this->action, $checkActionsConstants)) {
            $this->throwValidationException(
                $this,
                "Action type %s not supported. Supported checks are [%s].",
                $this->action,
                implode(', ', $checkActionsConstants)
            );
        }

        if ($this->reverseAction && $this->action === self::CHECK_ANY) {
            $this->throwValidationException(
                $this,
                "Action type %s not supported in the reverse mode. Use %s instead.",
                $this->action,
                self::CHECK_EQUALS
            );
        }

        // Validate the value
        if (empty($this->value)) {
            // If an empty value is specified (e.g. null, ""), we will use this check, only if the set action
            // is 'check_equals', 'check_empty', 'check_non_empty' or 'check_any'.
            $acceptsEmptyValue = [self::CHECK_ANY, self::CHECK_EQUALS, self::CHECK_EMPTY, self::CHECK_MISSING_KEY];
            if (!in_array($this->action, $acceptsEmptyValue)) {
                $this->throwValidationException(
                    $this,
                    "Action type %s not supported for empty values. " .
                    "Supported actions for empty values are [%s]",
                    $this->action,
                    implode(', ', $acceptsEmptyValue)
                );
            }
        }

        return true;
    }

    /**
     * Run the check. Check if the configured key has a value, in the previously set
     * "check-content", that respects the configured check action.
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
        $matched = false;

        try {
            $value = FileParser::getRequiredNestedArrayValue($this->key, $this->checkContent);

            // If no exceptions are thrown, then the key was found in the given config array
            switch($this->action) {
                case self::CHECK_CONTAINS:
                    $contains = $this->valueContains($value);
                    $matched = $this->reverseAction ? !$contains : $contains;
                    break;
                case self::CHECK_ENDS_WITH:
                    $endsWith = $this->valueEndsWith($value);
                    $matched = $this->reverseAction ? !$endsWith : $endsWith;
                    break;
                case self::CHECK_STARTS_WITH:
                    $startsWith = $this->valueStartsWith($value);
                    $matched = $this->reverseAction ? !$startsWith : $startsWith;
                    break;
                case self::CHECK_ANY:
                    /**
                     * At this point, if the reader has found a value, it means that the key is defined
                     * AND its value is either empty or not. So we can just return true. In case no action
                     * is set (empty string), we rely on the method getRequiredNestedConfigValue that returns
                     * a value only if the is define.
                     * REVERSE MODE IS NOT SUPPORTED FOR CHECK_ANY, SO WE DO NOT NEED TO REVERSE ITS ACTION.
                     */
                    $matched = true;
                    break;
                case self::CHECK_EQUALS:
                    $equalsTo = $this->valueEqualTo($value);
                    $matched = $this->reverseAction ? !$equalsTo : $equalsTo;
                    break;
                case self::CHECK_EMPTY:
                    $matched = $this->reverseAction ? !empty($value) : empty($value);
                    break;
                case self::CHECK_MISSING_KEY:
                    /**
                     * The only condition where we get to this point is that we
                     * are in the reverse mode (i.e. missing-key => not-missing key).
                     * In this case, the key is defined in the given array, so we
                     * can return true, which means that the given key is not missing.
                     */
                    // If reverse: Check missing -> Check NOT missing
                    $matched = ($this->reverseAction ? true : false);
                    break;
                default:
                    $this->throwWorkerException("Action type %s not supported.", $this->action);
            }
        } catch (MissingKeyException $missingKeyException) {
            if ($this->action === self::CHECK_MISSING_KEY) {
                $matched = ($this->reverseAction ? false : true);
            } else {
                // We throw an WorkerException that will be handled
                // in the run method (fatal and success-required)
                $this->throwWorkerException($missingKeyException->getMessage());
            }
        }

        return $actionResult->setResult($matched);
    }

    /**
     * Return a human-readable action description.
     *
     * @return string
     */
    protected function getReverseActionTag(): string
    {
        switch($this->action) {
            case self::CHECK_CONTAINS:
                return ($this->reverseAction ? "does not contain" : "contains");
                break;
            case self::CHECK_ENDS_WITH:
                return ($this->reverseAction ? "does not end" : "ends");
                break;
            case self::CHECK_EQUALS:
            case self::CHECK_EMPTY:
                return ($this->reverseAction ? "is not" : "is");
                break;
            case self::CHECK_MISSING_KEY:
                return ($this->reverseAction ? "is" : "is not");
                break;
            case self::CHECK_STARTS_WITH:
                return ($this->reverseAction ? "does not start" : "starts");
                break;
            default:
                return "not";
        }
    }

    /**
     * Check if the configured value contains the given value.
     * Supported value types are strings and arrays.
     *
     * @param mixed $searchValue The value that should be contained in the class value.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function valueContains($searchValue): bool
    {
        if (is_string($searchValue) && is_string($this->value)) {
            if (strpos($searchValue, $this->value) !== false) {
                return true;
            }
            return false;
        } elseif (is_array($searchValue)) {
            return array_key_exists($this->value, $searchValue);
        }
        $this->throwWorkerException(
            "Check %s supports only strings and arrays for both the configured and expected values.",
            self::CHECK_CONTAINS
        );
    }

    /**
     * Check if the configured value ends with the given value.
     *
     * @param mixed $value The value with which the class value should end.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function ValueEndsWith($value): bool
    {
        if (is_string($this->value) && is_string($value)) {
            return StringParser::endsWith($value, $this->value);
        }
        $this->throwWorkerException(
            "Check %s supports only strings for both the configured and expected values.",
            self::CHECK_ENDS_WITH
        );
    }

    /**
     * Check if the configured value starts with the given value.
     *
     * @param mixed $value The value with which the class value should start.
     *
     * @return bool
     *
     * @throws \Exception
     */
    protected function valueStartsWith($value): bool
    {
        if (is_string($this->value) && is_string($value)) {
            return StringParser::startsWith($value, $this->value);
        }
        $this->throwWorkerException(
            "Check %s supports only strings for both the configured and expected values.",
            self::CHECK_STARTS_WITH
        );
    }

    /**
     * Check if the configured value is equal to the given value.
     *
     * @param mixed $value The value to be compared.
     *
     * @return bool
     */
    protected function valueEqualTo($value): bool
    {
        if (is_numeric($this->value) && is_numeric($value)) {
            if ($this->value == $value) {
                return true;
            }
        }
        // We check on type
        if ($this->value === $value) {
            return true;
        }
        return false;
    }
}
