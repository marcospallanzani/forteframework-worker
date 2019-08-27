<?php

namespace Forte\Api\Generator\Transformers\Transforms\Checks;

use Forte\Api\Generator\Config\Reader;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\MissingConfigKeyException;
use Forte\Api\Generator\Exceptions\WrongConfigException;
use Forte\Api\Generator\Helpers\ClassConstantsTrait;
use Forte\Api\Generator\Helpers\ThrowsErrors;

/**
 * Class ArrayCheckParameters. Class used to wrap all required check parameters.
 *
 * @package Forte\Api\Generator\Transformers\Transforms\Checks
 */
class ArrayCheckParameters
{
    use ClassConstantsTrait, ThrowsErrors;

    /**
     * Supported operations.
     */
    const CHECK_STARTS_WITH  = "check_starts_with";
    const CHECK_ENDS_WITH    = "check_ends_with";
    const CHECK_CONTAINS     = "check_contains";
    const CHECK_EQUALS       = "check_equals";
    const CHECK_ANY          = "check_any";

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
    protected $operation;

    /**
     * ArrayCheckParameters constructor.
     *
     * @param string $key
     * @param null $value
     * @param string $operation
     */
    public function __construct(string $key, $value = null, string $operation = "")
    {
        $this->key = $key;
        $this->value = $value;
        $this->operation = $operation;
    }

    /**
     * Returns true if this ArrayCheckParameters instance is well configured and
     * respects the following rules:
     * - the field key must be specified and not empty;
     * - if a non-empty value is specified, an operation must be specified;
     * - if a non-empty value is specified, only operations different than 'content_any' are valid;
     * - if a non-empty value and an operation are specified, the operation must equal to one
     *   class constant starting with prefix 'CHECK_';
     * - if an empty value is specified (e.g. null, ""), the valid operations are 'content_equals'
     *   and 'content_any'; this case can be used to check if a given key is set and empty or null;
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
        } catch (\ReflectionException $reflectionException) {
            $this->throwGeneratorException(
                "A general error occurred while retrieving the checks list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        if (empty($this->getKey())) {
            $this->throwGeneratorException("You need to specify the 'key' for the following check: '%s'.", $this);
        }

        // If a non-empty value is specified, we need to check if an operation is set: if not, we throw an error.
        // If a non-empty value is set, we will use only operations different than 'content_any'.
        $operation = $this->getOperation();
        if (!empty($operation)) {
            if (!empty($this->getValue())) {
                // You need to specify one operation for non-empty values
                if (empty($operation)) {
                    // If value is not empty, at least one operation should be specified
                    $this->throwGeneratorException(
                        "You need to specify an operation for checks with non-empty values. " .
                        "Impacted check is: '%s'. Supported operations are: '%s'",
                        $this,
                        implode(',', $checkOperationsConstants)
                    );
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

                // If value is not empty, an operation different than 'content_any' should be specified
                if ($operation === ArrayCheckParameters::CHECK_ANY) {
                    unset($checkOperationsConstants['CHECK_ANY']);
                    $this->throwGeneratorException(
                        "The operation '%s' is not supported for non-empty values. Impacted check is: '%s'. " .
                        "Supported operations for non-empty values are: '%s'",
                        ArrayCheckParameters::CHECK_ANY,
                        $this,
                        implode(',', $checkOperationsConstants)
                    );
                }
            } else {
                // If an empty value is specified (e.g. null, ""), we will use this check, only if the set operation
                // is 'content_equals' or 'content_any'.
                if ($operation !== ArrayCheckParameters::CHECK_ANY
                    && $operation !== ArrayCheckParameters::CHECK_EQUALS
                ) {
                    $this->throwGeneratorException(
                        "The operation '%s' is not supported for empty values. Impacted check is: '%s'. " .
                        "Supported operations for empty values are: '%s'",
                        $operation,
                        $this,
                        implode(',', [ArrayCheckParameters::CHECK_ANY, ArrayCheckParameters::CHECK_EQUALS])
                    );
                }
            }
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

            $value = Reader::getRequiredNestedConfigValue($this->key, $config);
            // If no exceptions are thrown, then the key was found in the given config array
            switch($this->operation) {
                case self::CHECK_ANY:
                case "":
                    // At this point, if the reader has found a value, it means that the key is defined
                    // AND its value is either empty or not. So we can just return true.
                    // In case no operation is set (empty string), we rely on the method getRequiredNestedConfigValue
                    // that returns a value only if the is define.
                    return true;
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
                case self::CHECK_EQUALS:
                    if ($this->value === $value) {
                        return true;
                    }
                    return false;
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
                default:
                    $this->throwGeneratorException("It was not possible to verify the configured check condition.");
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Returns a string representation of this ArrayCheckParameters instance.
     *
     * @return false|string
     */
    public function __toString()
    {
        return sprintf(
            "Check if %skey '%s' %s",
            ($this->value ? "value with " : ""),
            $this->key,
            $this->getOperationMessage()
        );
    }

    /**
     * Returns a human-readable description of this check operation.
     *
     * @return string
     */
    protected function getOperationMessage(): string
    {
        switch($this->operation) {
            case self::CHECK_ANY:
                return "is set and has any value";
            case self::CHECK_CONTAINS:
                return "contains value '" . $this->value . "'";
            case self::CHECK_ENDS_WITH:
                return "ends with value '" . $this->value . "'";
            case self::CHECK_EQUALS:
                return "is equal to '" . $this->value . "'";
            case self::CHECK_STARTS_WITH:
                return "starts with value '" . $this->value . "'";
            default:
                return "exists";
        }
    }
}

