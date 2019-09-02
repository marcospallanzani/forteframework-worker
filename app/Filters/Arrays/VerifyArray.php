<?php

namespace Forte\Worker\Filters\Arrays;

use Forte\Worker\Exceptions\GeneratorException;
use Forte\Worker\Exceptions\MissingKeyException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Helpers\StringParser;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class VerifyArray. Class used to wrap all required check parameters.
 *
 * @package Forte\Worker\Filters\Arrays
 */
class VerifyArray extends AbstractArray
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
    const CHECK_ANY          = "check_any";
    const CHECK_MISSING_KEY  = "check_missing_key";

    /**
     * If true, the opposite check will be performed
     * (e.g. contains -> not-contains).
     *
     * @var bool
     */
    protected $reverseAction = false;

    /**
     * VerifyArray constructor.
     *
     * @param string $key The array key to access (multi-level keys separated by '.').
     * @param string $action The operation to perform (look inside isValid() implementation
     * for list of supported values).
     * @param mixed $value The value to set/change/remove.
     * @param bool $reverseAction Whether the reverse actions should be performed or not
     * (e.g. contains -> not-contains).
     */
    public function __construct(string $key, string $action, $value = null, bool $reverseAction = false)
    {
        parent::__construct($key, $action, $value);
        $this->reverseAction = $reverseAction;
    }

    /**
     * Validate the current VerifyArray instance. It returns true if this VerifyArray
     * instance is well configured and respects the following rules:
     * - the field key must be specified and not empty;
     * - the field action must be specified and not empty;
     * - if an empty value is specified (e.g. null, ""), the valid actions are
     *   'check_equals', 'check_empty' or 'check_any';
     *
     * The check `check_any` with an empty value can be used to check if a key is set.
     * The check `check_empty` with an empty value can be used to check if a key is set
     * and its value is empty or null.
     *
     * @return bool Returns true, if this check parameters are correctly
     * configured and consistent; false otherwise.
     *
     * @throws GeneratorException
     */
    public function isValid(): bool
    {
        // Validate the key
        if (empty($this->key)) {
            $this->throwGeneratorException("You need to specify the 'key' for check: '%s'.", $this);
        }

        // Validate the action.
        $action = $this->getAction();
        if (empty($this->action)) {
            $this->throwGeneratorException("You need to specify the 'action' for check: '%s'.", $this);
        }

        // If no action is specified OR an unsupported action is given, then we throw an error.
        $checkActionsConstants = $this->getSupportedActions();
        if (!in_array($action, $checkActionsConstants)) {
            $this->throwGeneratorException(
                "The check '%s' is not supported. Impacted check is: '%s'. Supported checks are: '%s'",
                $action,
                $this,
                implode(',', $checkActionsConstants)
            );
        }

        if ($this->reverseAction && $action === self::CHECK_ANY) {
            $this->throwGeneratorException(
                "The check '%s' is not supported in the reverse mode. Use '%s' instead. Impacted check is: '%s'.",
                $action,
                self::CHECK_EQUALS,
                $this
            );
        }

        // Validate the value
        if (empty($this->getValue())) {
            // If an empty value is specified (e.g. null, ""), we will use this check, only if the set action
            // is 'check_equals', 'check_empty', 'check_non_empty' or 'check_any'.
            $acceptsEmptyValue = [self::CHECK_ANY, self::CHECK_EQUALS, self::CHECK_EMPTY, self::CHECK_MISSING_KEY];
            if (!in_array($action, $acceptsEmptyValue)) {
                $this->throwGeneratorException(
                    "The action '%s' is not supported for empty values. Impacted check is: '%s'. " .
                    "Supported actions for empty values are: '%s'",
                    $action,
                    $this,
                    implode(', ', $acceptsEmptyValue)
                );
            }
        }

        return true;
    }

    /**
     * Check if the configured key has a value in the given array,
     * that respects the configured action.
     *
     * @param array $array The array containing the keys to be checked.
     *
     * @return bool
     *
     * @throws GeneratorException
     * @throws MissingKeyException
     */
    public function checkCondition(array $array): bool
    {
        if ($this->isValid()) {

            try {
                $value = FileParser::getRequiredNestedConfigValue($this->key, $array);

                // If no exceptions are thrown, then the key was found in the given config array
                switch($this->action) {
                    case self::CHECK_CONTAINS:
                        $contains = $this->contains($value);
                        return $this->reverseAction ? !$contains : $contains;
                        break;
                    case self::CHECK_ENDS_WITH:
                        $endsWith = $this->endsWith($value);
                        return $this->reverseAction ? !$endsWith : $endsWith;
                        break;
                    case self::CHECK_STARTS_WITH:
                        $startsWith = $this->startsWith($value);
                        return $this->reverseAction ? !$startsWith : $startsWith;
                        break;
                    case self::CHECK_ANY:
                        /**
                         * At this point, if the reader has found a value, it means that the key is defined
                         * AND its value is either empty or not. So we can just return true. In case no action
                         * is set (empty string), we rely on the method getRequiredNestedConfigValue that returns
                         * a value only if the is define.
                         * REVERSE MODE IS NOT SUPPORTED FOR CHECK_ANY, SO WE DO NOT NEED TO REVERSE ITS ACTION.
                         */
                        return true;
                        break;
                    case self::CHECK_EQUALS:
                        $equalsTo = $this->equalsTo($value);
                        return $this->reverseAction ? !$equalsTo : $equalsTo;
                        break;
                    case self::CHECK_EMPTY:
                        return $this->reverseAction ? !empty($value) : empty($value);
                        break;
                    case self::CHECK_MISSING_KEY:
                        /**
                         * The only condition where we get to this point is that we
                         * are in the reverse mode (i.e. missing-key => not-missing key).
                         * In this case, the key is defined in the given array, so we
                         * can return true, which means that the given key is not missing.
                         */
                        return true;
                        break;
                    default:
                        $this->throwGeneratorException(sprintf(
                            "It was not possible to verify the configured check condition. Impacted check is: '%s'",
                            $this
                        ));
                        break;
                }
            } catch (MissingKeyException $missingKeyException) {
                if ($this->action === self::CHECK_MISSING_KEY) {
                    return true;
                }
                throw $missingKeyException;
            }
        }

        return false;
    }

    /**
     * Return a human-readable description of this check action.
     *
     * @return string
     */
    public function getActionMessage(): string
    {
        $baseMessage = "Check if key '" . $this->key . "' is set";
        $reverseAction = $this->getReverseActionTag();
        switch($this->action) {
            case self::CHECK_ANY:
                return $baseMessage . " and has any value";
            case self::CHECK_CONTAINS:
                return $baseMessage . " and $reverseAction value '" . $this->stringifyValue() . "'";
            case self::CHECK_ENDS_WITH:
            case self::CHECK_STARTS_WITH:
                return $baseMessage . " and $reverseAction with value '" . $this->stringifyValue() . "'";
            case self::CHECK_EQUALS:
                return $baseMessage . " and $reverseAction equal to value '" . $this->stringifyValue() . "'";
            case self::CHECK_EMPTY:
                return $baseMessage . " and $reverseAction empty (empty string or null)";
            case self::CHECK_MISSING_KEY:
                return "Check if key '" . $this->key . "' $reverseAction set";
            default:
                return $baseMessage;
        }
    }

    /**
     * Return a list of all available actions.
     *
     * @return array
     *
     * @throws GeneratorException
     */
    public function getSupportedActions(): array
    {
        try {
            return self::getClassConstants('CHECK_');
        } catch (\ReflectionException $reflectionException) {
            $this->throwGeneratorException(
                "An error occurred while retrieving the list of supported actions. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }
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
     * @param mixed $value The value that should be contained in the class value.
     *
     * @return bool
     *
     * @throws GeneratorException
     */
    protected function contains($value): bool
    {
        if (is_string($this->value)) {
            if (strpos($value, $this->value) !== false) {
                return true;
            }
            return false;
        } elseif (is_array($this->value)) {
            return in_array($value, $this->value);
        }

        $this->throwGeneratorException(
            "It was not possible to verify if the value for key '%s' contains the configured value. ".
            "The check '%s' supports only strings and arrays for both the configured and expected values.",
            $this->key,
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
     * @throws GeneratorException
     */
    protected function endsWith($value): bool
    {
        if (is_string($this->value) && is_string($value)) {
            return StringParser::endsWith($value, $this->value);
        }
        $this->throwGeneratorException(
            "It was not possible to verify if the value for key '%s' ends with the configured value. ".
            "The check '%s' supports only strings for both the configured and expected values.",
            $this->key,
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
     * @throws GeneratorException
     */
    protected function startsWith($value): bool
    {
        if (is_string($this->value) && is_string($value)) {
            return StringParser::startsWith($value, $this->value);
        }
        $this->throwGeneratorException(
            "It was not possible to verify if the value for key '%s' starts with the configured value. ".
            "The check '%s' supports only strings for both the configured and expected values.",
            $this->key,
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
    protected function equalsTo($value): bool
    {
        if ($this->value === $value) {
            return true;
        }
        return false;
    }
}
