<?php

namespace Forte\Api\Generator\Filters\Arrays;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\MissingConfigKeyException;
use Forte\Api\Generator\Exceptions\WrongConfigException;
use Forte\Api\Generator\Helpers\ClassAccessTrait;
use Forte\Api\Generator\Helpers\FileParser;
use Forte\Api\Generator\Helpers\ThrowErrors;

/**
 * Class VerifyArray. Class used to wrap all required check parameters.
 *
 * @package Forte\Api\Generator\Filters\Arrays
 */
class VerifyArray extends AbstractArray
{
    use ClassAccessTrait, ThrowErrors;

    /**
     * Supported operations.
     */
    const CHECK_STARTS_WITH  = "check_starts_with";
    const CHECK_ENDS_WITH    = "check_ends_with";
    const CHECK_CONTAINS     = "check_contains";
    const CHECK_EQUALS       = "check_equals";
    const CHECK_EMPTY        = "check_empty";
    const CHECK_NON_EMPTY    = "check_non_empty";
    const CHECK_ANY          = "check_any";

    /**
     * Returns true if this VerifyArray instance is well configured and
     * respects the following rules:
     * - the field key must be specified and not empty;
     * - the field operation must be specified and not empty;
     * - if an empty value is specified (e.g. null, ""), the valid operations are
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
        try {
            $checkOperationsConstants = self::getClassConstants('CHECK_');

            // Validate the key
            if (empty($this->key)) {
                $this->throwGeneratorException("You need to specify the 'key' for check: '%s'.", $this);
            }

            // Validate the operation.
            $operation = $this->getOperation();
            if (empty($this->operation)) {
                $this->throwGeneratorException("You need to specify the 'operation' for check: '%s'.", $this);
            }

            // If no operation is specified OR an unsupported operation is given, then we throw an error.
            if (!in_array($operation, $checkOperationsConstants)) {
                $this->throwGeneratorException(
                    "The check '%s' is not supported. Impacted check is: '%s'. Supported checks are: '%s'",
                    $operation,
                    $this,
                    implode(',', $checkOperationsConstants)
                );
            }

            // Validate the value
            if (empty($this->getValue())) {
                // If an empty value is specified (e.g. null, ""), we will use this check, only if the set operation
                // is 'check_equals', 'check_empty', 'check_non_empty' or 'check_any'.
                $acceptsEmptyValue = [self::CHECK_ANY, self::CHECK_EQUALS, self::CHECK_EMPTY, self::CHECK_NON_EMPTY];
                if (!in_array($operation, $acceptsEmptyValue)) {
                    $this->throwGeneratorException(
                        "The operation '%s' is not supported for empty values. Impacted check is: '%s'. " .
                        "Supported operations for empty values are: '%s'",
                        $operation,
                        $this,
                        implode(', ', $acceptsEmptyValue)
                    );
                }
            }
        } catch (\ReflectionException $reflectionException) {
            $this->throwGeneratorException(
                "A general error occurred while retrieving the checks list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        return true;
    }

    /**
     * Checks if the configured key has a value, that respects the configured operation.
     *
     * @param array $config The array containing the configuration keys to be checked.
     *
     * @return bool
     *
     * @throws GeneratorException
     * @throws MissingConfigKeyException
     * @throws WrongConfigException
     */
    public function checkCondition(array $config): bool
    {
        if ($this->isValid()) {

            $value = FileParser::getRequiredNestedConfigValue($this->key, $config);
            // If no exceptions are thrown, then the key was found in the given config array
            switch($this->operation) {
                case self::CHECK_CONTAINS:
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
                    break;
                case self::CHECK_ENDS_WITH:
                    if (is_string($this->value) && is_string($value)) {
                        if (substr_compare($this->value, $value, -strlen($value)) === 0) {
                            return true;
                        }
                        return false;
                    }
                    $this->throwGeneratorException(
                        "It was not possible to verify if the value for key '%s' ends with the configured value. ".
                        "The check '%s' supports only strings for both the configured and expected values.",
                        $this->key,
                        self::CHECK_ENDS_WITH
                    );
                    break;
                case self::CHECK_STARTS_WITH:
                    if (is_string($this->value) && is_string($value)) {
                        if (substr_compare($this->value, $value, -strlen($value)) === 0) {
                            return true;
                        }
                        return false;
                    }
                    $this->throwGeneratorException(
                        "It was not possible to verify if the value for key '%s' starts with the configured value. ".
                        "The check '%s' supports only strings for both the configured and expected values.",
                        $this->key,
                        self::CHECK_STARTS_WITH
                    );
                    break;
                case self::CHECK_ANY:
                    /**
                     * At this point, if the reader has found a value, it means that the key is defined
                     * AND its value is either empty or not. So we can just return true. In case no operation
                     * is set (empty string), we rely on the method getRequiredNestedConfigValue that returns
                     * a value only if the is define.
                     */
                    return true;
                    break;
                case self::CHECK_EQUALS:
                    if ($this->value === $value) {
                        return true;
                    }
                    return false;
                    break;
                case self::CHECK_EMPTY:
                    if (empty($value)) {
                        return true;
                    }
                    return false;
                    break;
                case self::CHECK_NON_EMPTY:
                    if (!empty($value)) {
                        return true;
                    }
                    return false;
                    break;
                default:
                    $this->throwGeneratorException("It was not possible to verify the configured check condition.");
                    break;
            }
        }

        return false;
    }

    /**
     * Returns a human-readable description of this check operation.
     *
     * @return string
     */
    public function getOperationMessage(): string
    {
        $baseMessage = "Check if key '" . $this->key . "' is set";
        switch($this->operation) {
            case self::CHECK_ANY:
                return $baseMessage . " and has any value";
            case self::CHECK_CONTAINS:
                return $baseMessage . " and contains value '" . $this->stringifyValue() . "'";
            case self::CHECK_ENDS_WITH:
                return $baseMessage . " and ends with value '" . $this->stringifyValue() . "'";
            case self::CHECK_EQUALS:
                return $baseMessage . " and is equal to value '" . $this->stringifyValue() . "'";
            case self::CHECK_EMPTY:
                return $baseMessage . " and is empty (empty string or null)";
            case self::CHECK_NON_EMPTY:
                return $baseMessage . " and is not empty (not empty string, not null)";
            case self::CHECK_STARTS_WITH:
                return $baseMessage . " and starts with value '" . $this->stringifyValue() . "'";
            default:
                return $baseMessage;
        }
    }
}
