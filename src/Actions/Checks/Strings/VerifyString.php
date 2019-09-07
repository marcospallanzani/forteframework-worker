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

namespace Forte\Worker\Actions\Checks\Strings;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Helpers\StringParser;

/**
 * Class VerifyString. This class describes a check condition to be executed
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
 * - ends with;
 * - starts with;
 * - regex;
 * - is_empty;
 *
 * This check can be used to compare also text representing a versioning.
 * In this case, all filters can be used to compare two version strings
 * (e.g. 1.0.1 < 1.0.2 will return true).
 *
 * All of the above conditions, except for "is_empty", require a condition value.
 *
 * @package Forte\Worker\Actions\Checks\Strings
 */
class VerifyString extends AbstractAction
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
    const CONDITION_STARTS_WITH          = "starts_with";
    const CONDITION_ENDS_WITH            = "ends_with";
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
     * VerifyString constructor.
     *
     * @param string $condition
     * @param string $conditionValue
     * @param string $initialContent
     */
    public function __construct(
        string $condition,
        $conditionValue = "",
        string $initialContent = ""
    ) {
        parent::__construct();
        $this->condition      = $condition;
        $this->conditionValue = $conditionValue;
        $this->content        = $initialContent;
    }

    /**
     * Set the content to be checked. This method is useful to update
     * a VerifyString instance with a new content, to verify the configured
     * condition against the new content.
     *
     * @param string $content The content to be checked.
     *
     * @return VerifyString
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Return the condition value.
     *
     * @return mixed|string
     */
    public function getConditionValue()
    {
        return $this->conditionValue;
    }

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        switch ($this->condition) {
            case self::CONDITION_EQUAL_TO:
                $actionDescription = "is equal to the specified check value '%s'";
                break;
            case self::CONDITION_LESS_THAN:
                $actionDescription = "is less than the specified check value '%s'";
                break;
            case self::CONDITION_LESS_EQUAL_THAN:
                $actionDescription = "is less than or equal to the specified check value '%s'";
                break;
            case self::CONDITION_GREATER_THAN:
                $actionDescription = "is greater than the specified check value '%s'";
                break;
            case self::CONDITION_GREATER_EQUAL_THAN:
                $actionDescription = "is greater than or equal to the specified check value '%s'";
                break;
            case self::CONDITION_DIFFERENT_THAN:
                $actionDescription = "is different than the specified check value '%s'";
                break;
            case self::CONDITION_CONTAINS:
                $actionDescription = "contains the specified check value '%s'";
                break;
            case self::CONDITION_STARTS_WITH:
                $actionDescription = "starts with the specified check value '%s'";
                break;
            case self::CONDITION_ENDS_WITH:
                $actionDescription = "ends with the specified check value '%s'";
                break;
            case self::CONDITION_IS_EMPTY:
                $actionDescription = "is empty";
                break;
            case self::CONDITION_REGEX:
                $actionDescription = "respects the given regex \"%s\"";
                break;
            default:
                $actionDescription = "";
        }

        if ($actionDescription === "") {
            return "Unsupported condition.";
        }

        return sprintf(
            "Check if the given content '%s' $actionDescription.",
            $this->content,
            $this->conditionValue
        );
    }

    /**
     * Validate the current action result. This method returns true if the last
     * execution of the apply() method executed correctly (i.e. the configured
     * check condition on the given string was met); false otherwise.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of this VerifyString instance.
     *
     * @return bool True if the last execution of the apply() method executed
     * correctly (i.e. the configured check condition on the given string was
     * met); false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        // The ActionResult->result field should be set with a boolean
        // representing the last execution of the apply method.
        return (bool) $actionResult->getResult();
    }

    /**
     * Return an associative array of all available conditions. Possible values
     * are class constants, that begin by "CONDITION_".
     *
     * @return array Conditions list.
     */
    public function getSupportedConditions(): array
    {
        return self::getClassConstants('CONDITION_');
    }

    /**
     * Run the check.
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
        switch ($this->condition) {
            case self::CONDITION_EQUAL_TO:
                return $actionResult->setResult($this->content === $this->conditionValue);
                break;
            case self::CONDITION_LESS_THAN:
                return $actionResult->setResult($this->content < $this->conditionValue);
                break;
            case self::CONDITION_LESS_EQUAL_THAN:
                return $actionResult->setResult($this->content <= $this->conditionValue);
                break;
            case self::CONDITION_GREATER_THAN:
                return $actionResult->setResult($this->content > $this->conditionValue);
                break;
            case self::CONDITION_GREATER_EQUAL_THAN:
                return $actionResult->setResult($this->content >= $this->conditionValue);
                break;
            case self::CONDITION_DIFFERENT_THAN:
                return $actionResult->setResult($this->content !== $this->conditionValue);
                break;
            case self::CONDITION_CONTAINS:
                $found = strpos($this->content, $this->conditionValue);
                $conditionMet = ((!is_bool($found) && strpos($this->content, $this->conditionValue) >= 0) ? true : false);
                return $actionResult->setResult($conditionMet);
                break;
            case self::CONDITION_STARTS_WITH:
                return $actionResult->setResult(StringParser::startsWith($this->content, $this->conditionValue));
                break;
            case self::CONDITION_ENDS_WITH:
                return $actionResult->setResult(StringParser::endsWith($this->content, $this->conditionValue));
                break;
            case self::CONDITION_IS_EMPTY:
                return $actionResult->setResult(empty($this->content));
                break;
            case self::CONDITION_REGEX:
                return $actionResult->setResult((bool) (preg_match($this->conditionValue, $this->content)));
                break;
            default:
                $this->throwWorkerException("Condition type %s not supported.", $this->condition);
        }
        return $actionResult->setResult(false);
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if the implementing class instance
     * was well configured; false otherwise.
     *
     * @throws \Exception If the implementing class instance
     * was not well configured.
     */
    protected function validateInstance(): bool
    {
        // If no action is specified OR an unsupported action is given, then we throw an error.
        $supportedConditions = $this->getSupportedConditions();
        if (!in_array($this->condition, $supportedConditions)) {
            $this->throwWorkerException(
                "Condition %s not supported. Supported conditions are [%s]",
                $this->condition,
                implode(', ', $supportedConditions)
            );
        }

        /**
         * If condition is not CONDITION_IS_EMPTY, then we need to specify a condition
         * value. It makes no sense to perform a check condition "equal_to" without a
         * condition value (e.g. check if XXX is equal to YYY).
         */
        if ($this->condition !== self::CONDITION_IS_EMPTY && empty($this->conditionValue)) {
            $this->throwWorkerException(
                "Condition %s requires a condition value. " .
                "Empty value accepted only for condition %s.",
                $this->condition,
                self::CONDITION_IS_EMPTY
            );
        }

        return true;
    }
}
