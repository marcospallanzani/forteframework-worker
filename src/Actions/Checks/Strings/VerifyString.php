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
use Forte\Worker\Helpers\StringHelper;

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
     * @var bool
     */
    protected $caseSensitive = false;

    /**
     * VerifyString constructor.
     *
     * @param string $condition The condition to be checked (class constants starting
     * with CONDITION).
     * @param string $conditionValue The value used in the condition evaluation
     * (e.g. if [value] equal to [condition value]).
     * @param string $initialContent The content to be evaluated (e.g. if [initial content]
     * equal to [condition value]).
     * @param bool $caseSensitive True, the condition evaluation will be case sensitive;
     * false, case-insensitive evaluation. This flag applies to all conditions except
     * CONDITION_REGEX and CONDITION_IS_EMPTY.
     */
    public function __construct(
        string $condition = "",
        $conditionValue = "",
        string $initialContent = "",
        bool $caseSensitive = false
    ) {
        parent::__construct();
        $this->condition      = $condition;
        $this->conditionValue = $conditionValue;
        $this->content        = $initialContent;
        $this->caseSensitive  = $caseSensitive;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * starts with the given value.
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function startsWith(string $value): self
    {
        $this->condition      = self::CONDITION_STARTS_WITH;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * ends with the given value.
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function endsWith(string $value): self
    {
        $this->condition      = self::CONDITION_ENDS_WITH;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * contains the given value.
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function contains(string $value): self
    {
        $this->condition      = self::CONDITION_CONTAINS;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is equal to the given value.
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function isEqualTo(string $value): self
    {
        $this->condition      = self::CONDITION_EQUAL_TO;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is less than the given value.
     *
     * This check can be used to compare also text representing versions.
     * (e.g. 1.0.1 < 1.0.2 will return true).
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function isLessThan(string $value): self
    {
        $this->condition      = self::CONDITION_LESS_THAN;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is less than / equal to the given value.
     *
     * This check can be used to compare also text representing versions.
     * (e.g. 1.0.1 <= 1.0.2 will return true).
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function isLessThanEqualTo(string $value): self
    {
        $this->condition      = self::CONDITION_LESS_EQUAL_THAN;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is greater than the given value.
     *
     * This check can be used to compare also text representing versions.
     * (e.g. 1.0.2 > 1.0.1 will return true).
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function isGreaterThan(string $value): self
    {
        $this->condition      = self::CONDITION_GREATER_THAN;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is greater than / equal to the given value.
     *
     * This check can be used to compare also text representing versions.
     * (e.g. 1.0.2 >= 1.0.1 will return true).
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function isGreaterThanEqualTo(string $value): self
    {
        $this->condition      = self::CONDITION_GREATER_EQUAL_THAN;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is different than the given value.
     *
     * This check can be used to compare also text representing versions.
     * (e.g. 1.0.2 <> 1.0.1 will return true).
     *
     * @param mixed $value The value to be matched by the action condition.
     *
     * @return VerifyString
     */
    public function isDifferentThan(string $value): self
    {
        $this->condition      = self::CONDITION_DIFFERENT_THAN;
        $this->conditionValue = $value;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * matches the given regular expression.
     *
     * @param mixed $regex The regex to be used to parse the specified check content.
     *
     * @return VerifyString
     */
    public function matchesReqex(string $regex): self
    {
        $this->condition      = self::CONDITION_REGEX;
        $this->conditionValue = $regex;

        return $this;
    }

    /**
     * Set this VerifyString instance, so that it checks if the check content
     * is empty.
     *
     * @return VerifyString
     */
    public function isEmpty(): self
    {
        $this->condition = self::CONDITION_IS_EMPTY;

        return $this;
    }

    /**
     * Set the case-sensitive flag with the specified value.
     *
     * @param bool $caseSensitive Whether the check action should be case sensitive or not.
     *
     * @return $this
     */
    public function caseSensitive(bool $caseSensitive = true): self
    {
        $this->caseSensitive = $caseSensitive;

        return $this;
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
    public function checkContent(string $content): self
    {
        $this->content = $content;

        return $this;
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
                return $this->formatActionDescription(
                    "is equal to the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_LESS_THAN:
                return $this->formatActionDescription(
                    "is less than the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_LESS_EQUAL_THAN:
                return $this->formatActionDescription(
                    "is less than or equal to the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_GREATER_THAN:
                return $this->formatActionDescription(
                    "is greater than the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_GREATER_EQUAL_THAN:
                return $this->formatActionDescription(
                    "is greater than or equal to the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_DIFFERENT_THAN:
                return $this->formatActionDescription(
                    "is different than the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_CONTAINS:
                return $this->formatActionDescription(
                    "contains the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_STARTS_WITH:
                return $this->formatActionDescription(
                    "starts with the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_ENDS_WITH:
                return $this->formatActionDescription(
                    "ends with the specified check value '%s'",
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                );
            case self::CONDITION_IS_EMPTY:
                return $this->formatActionDescription("is empty", $this->content);
            case self::CONDITION_REGEX:
                return $this->formatActionDescription(
                    "respects the given regex \"%s\"",
                    $this->content,
                    $this->conditionValue
                );
            default:
                return "Unsupported condition.";
        }
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
     * Format the given action description with the specified parameters.
     *
     * @param string $partialActionDescription
     * @param string $content
     * @param string|null $contentValue
     * @param bool|null $caseSensitive
     *
     * @return string
     */
    protected function formatActionDescription(
        string $partialActionDescription,
        string $content,
        string $contentValue = null,
        bool $caseSensitive = null
    ): string
    {
        $arguments[] = $content;
        if (is_string($contentValue)) {
            $arguments[] = $contentValue;
        }

        $description = vsprintf("Check if the given content '%s' $partialActionDescription.", $arguments);
        if ($caseSensitive === true) {
            $description = rtrim($description, '.') . " (case sensitive).";
        } elseif ($caseSensitive === false) {
            $description = rtrim($description, '.') . " (case insensitive).";
        }

        return $description;
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
                return $actionResult->setResult(StringHelper::equalTo(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_LESS_THAN:
                return $actionResult->setResult(StringHelper::lessThan(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_LESS_EQUAL_THAN:
                return $actionResult->setResult(StringHelper::lessThanEqualTo(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_GREATER_THAN:
                return $actionResult->setResult(StringHelper::greaterThan(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_GREATER_EQUAL_THAN:
                return $actionResult->setResult(StringHelper::greaterThanEqualTo(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_DIFFERENT_THAN:
                return $actionResult->setResult(StringHelper::differentThan(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_CONTAINS:
                return $actionResult->setResult(StringHelper::contains(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_STARTS_WITH:
                return $actionResult->setResult(StringHelper::startsWith(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_ENDS_WITH:
                return $actionResult->setResult(StringHelper::endsWith(
                    $this->content,
                    $this->conditionValue,
                    $this->caseSensitive
                ));
            case self::CONDITION_IS_EMPTY:
                return $actionResult->setResult(empty($this->content));
            case self::CONDITION_REGEX:
                return $actionResult->setResult((bool) (preg_match($this->conditionValue, $this->content)));
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
            $this->throwValidationException(
                $this,
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
            $this->throwValidationException(
                $this,
                "Condition %s requires a condition value. " .
                "Empty value accepted only for condition %s.",
                $this->condition,
                self::CONDITION_IS_EMPTY
            );
        }

        return true;
    }
}
