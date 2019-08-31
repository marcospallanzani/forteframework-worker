<?php
/**
 * This file is part of the ForteFramework package.
 *
 * Copyright (c) 2019  Marco Spallanzani <marco@forteframework.com>
 *
 *  For the full copyright and license information,
 *  please view the LICENSE file that was distributed
 *  with this source code.
 */

namespace Forte\Api\Generator\Checkers\Checks\Text;

use Forte\Api\Generator\Checkers\Checks\AbstractCheck;
use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;

/**
 * Class VerifyText. This class describes a check condition to be executed
 * on a given input text.
 *
 * The supported conditions are the class constants that begin with "CONDITION_XXX".
 * Here is the list of all available conditions:
 * - equal_to;
 * - less_than;
 * - less_equal_than;
 * - greater_than;
 * - greater_equal_than;
 * - different_than;
 * - contains;
 * - regex;
 * - is_empty;
 *
 * All of the above conditions, except for "is_empty", require a condition value.
 *
 * @package Forte\Api\Generator\Checkers\Checks\Text
 */
class VerifyText extends AbstractCheck
{
    /**
     * Supported conditions.
     */
    const CONDITION_EQUAL_TO             = "equal_to";
    const CONDITION_LESS_THAN            = "less_than";
    const CONDITION_LESS_EQUAL_THAN      = "less_equal_than";
    const CONDITION_GREATER_THAN         = "greater_than";
    const CONDITION_GREATER_EQUAL_THAN   = "greater_equal_than";
    const CONDITION_DIFFERENT_THAN       = "different_than";
    const CONDITION_CONTAINS             = "contains";
    const CONDITION_IS_EMPTY             = "is_empty";
    const CONDITION_REGEX                = "regex";

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $condition;

    /**
     * @var mixed
     */
    protected $conditionValue;

    /**
     * VerifyText constructor.
     *
     * @param string $initialContent
     * @param string $condition
     * @param string $conditionValue
     */
    public function __construct(string $initialContent, string $condition, $conditionValue = "")
    {
        $this->content        = $initialContent;
        $this->condition      = $condition;
        $this->conditionValue = $conditionValue;
    }

    /**
     * Set the content to be checked. This method is useful to update
     * a VerifyText instance with a new content, to verify the configured
     * condition against the new content.
     *
     * @param string $content The content to be checked.
     *
     * @return VerifyText
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Run the check.
     *
     * @return bool True if this AbstractCheck subclass instance
     * ran successfully; false otherwise.
     *
     * @throws CheckException If this AbstractCheck subclass instance
     * check did not run successfully.
     * @throws GeneratorException Unsupported condition.
     */
    protected function check(): bool
    {
        switch ($this->condition) {
            case self::CONDITION_EQUAL_TO:
                return ($this->content === $this->conditionValue);
                break;
            case self::CONDITION_LESS_THAN:
                // We try to convert the given content and the given
                // condition value to numbers and then we compare them.
//                list($content, $conditionValue) = $this->getNumericCheckValues();
//                return ($content && $conditionValue && $content < $conditionValue);
                return ($this->content < $this->conditionValue);
                break;
            case self::CONDITION_LESS_EQUAL_THAN:
//                list($content, $conditionValue) = $this->getNumericCheckValues();
//                return ($content && $conditionValue && $content <= $conditionValue);
                return ($this->content <= $this->conditionValue);
                break;
            case self::CONDITION_GREATER_THAN:
//                list($content, $conditionValue) = $this->getNumericCheckValues();
//                return ($content && $conditionValue && $content > $conditionValue);
                return ($this->content > $this->conditionValue);
                break;
            case self::CONDITION_GREATER_EQUAL_THAN:
//                list($content, $conditionValue) = $this->getNumericCheckValues();
//                return ($content && $conditionValue && $content >= $conditionValue);
                return ($this->content >= $this->conditionValue);
                break;
            case self::CONDITION_DIFFERENT_THAN:
                return ($this->content !== $this->conditionValue);
                break;
            case self::CONDITION_CONTAINS:
                $found = strpos($this->content, $this->conditionValue);
                return ((!is_bool($found) && strpos($this->content, $this->conditionValue) >= 0) ? true : false);
                break;
            case self::CONDITION_IS_EMPTY:
                return empty($this->content);
                break;
            case self::CONDITION_REGEX:
                return  (bool) (preg_match($this->conditionValue, $this->content));
                break;
            default:
                $this->throwGeneratorException("Unsupported condition '%s'", $this->condition);
                return "";
        }
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if the implementing class instance
     * was well configured; false otherwise.
     *
     * @throws GeneratorException If the implementing class
     * instance was not well configured.
     */
    public function isValid(): bool
    {
        // If no action is specified OR an unsupported action is given, then we throw an error.
        $supportedConditions = $this->getSupportedConditions();
        if (!in_array($this->condition, $supportedConditions)) {
            $this->throwGeneratorException(
                "The condition '%s' is not supported. Impacted check is: '%s'. Supported conditions are: '%s'",
                $this->condition,
                $this,
                implode(',', $supportedConditions)
            );
        }

        /**
         * If condition is not CONDITION_IS_EMPTY, then we need to specify a condition
         * value. It makes no sense to perform a check condition "equal_to" without a
         * condition value (e.g. check if XXX is equal to YYY).
         */
        if ($this->condition !== self::CONDITION_IS_EMPTY && empty($this->conditionValue)) {
            $this->throwGeneratorException(
                "The condition '%s' requires a condition value. " .
                "Condition value can be empty only for condition '%s'.",
                $this->condition,
                self::CONDITION_IS_EMPTY
            );
        }

        return true;
    }

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     *
     * @throws GeneratorException The configured condition is not supported.
     */
    public function stringify(): string
    {
        switch ($this->condition) {
            case self::CONDITION_EQUAL_TO:
                return sprintf(
                    "Check if the given content '%s' is equal to the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_LESS_THAN:
                return sprintf(
                    "Check if the given content '%s' is less than the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_LESS_EQUAL_THAN:
                return sprintf(
                    "Check if the given content '%s' is less than or equal to the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_GREATER_THAN:
                return sprintf(
                    "Check if the given content '%s' is greater than the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_GREATER_EQUAL_THAN:
                return sprintf(
                    "Check if the given content '%s' is greater than or equal to the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_DIFFERENT_THAN:
                return sprintf(
                    "Check if the given content '%s' is different than the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_CONTAINS:
                return sprintf(
                    "Check if the given content '%s' contains the specified check value '%s'.",
                    $this->content,
                    $this->conditionValue
                );
                break;
            case self::CONDITION_IS_EMPTY:
                return sprintf(
                    "Check if the given content '%s' is empty.",
                    $this->content
                );
                break;
            case self::CONDITION_REGEX:
                return sprintf(
                    "Check if the given content '%s' respects the given regex \"%s\".",
                    $this->content,
                    $this->conditionValue
                );
                break;
            default:
                $this->throwGeneratorException("Unsupported condition '%s'", $this->condition);
                return "";
        }
    }

    /**
     * Return an associative array of all available conditions. Possible values
     * are class constants, that begin by "CONDITION_".
     *
     * @return array Conditions list.
     *
     * @throws GeneratorException
     */
    public function getSupportedConditions(): array
    {
        try {
            return self::getClassConstants('CONDITION_');
        } catch (\ReflectionException $reflectionException) {
            $this->throwGeneratorException(
                "An error occurred while retrieving the list of supported conditions. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }
    }
}
