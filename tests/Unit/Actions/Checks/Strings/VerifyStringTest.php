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

namespace Forte\Worker\Tests\Unit\Actions\Checks\Strings;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class VerifyStringTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Checks\Strings
 */
class VerifyStringTest extends BaseTest
{
    /**
     * Data provider for general check tests.
     *
     * @return array
     */
    public function conditionsProvider(): array
    {
        $content1 = "test1";
        $content2 = "test2";
        $conditionValue1 = "test1";
        $conditionValue2 = "test2";

        $longContent1 = "this is a longer test1 to check the contains or regex conditions with numeric value 1.01.";
        $longContent2 = "this is a longer test2 to check the contains or regex conditions with numeric value 1.02.";
        $longContent3 = "this is a longer test with no numbers at all to check the contains condition.";
        $longContent4 = "this is a longer test with no numbers but with a version 1.0.1.21 to check the contains condition.";
        $decimalValueRegex = '/(\w+)[-+]?([0-9]*\.[0-9]+|[0-9]+)/';

        $numericContent1 = "1.01";
        $numericContent2 = "1.02";
        $numericConditionValue1 = "1.01";
        $numericConditionValue2 = "1.02";

        $versionContent1 = "1.0.1.21";
        $versionContent2 = "1.0.2.21";
        $versionConditionValue1 = "1.0.1.21";
        $versionConditionValue2 = "1.0.2.21";

        $caseSensitiveMessage = "(case insensitive)";

        return [
            /** CONDITION_EQUAL_TO tests */
            // Condition | condition value | initial content | is valid | severity | expected | validation exception | object message | case sensitive
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is equal to the specified check value '$conditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, $versionConditionValue1, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent1' is equal to the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is equal to the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is equal to the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '".strtoupper($content1)."' is equal to the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '".strtoupper($content1)."' is equal to the specified check value '$conditionValue1' (case sensitive).", true],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue2' (case sensitive).", true],
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue1, $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content2' is equal to the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, $versionConditionValue2, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$versionContent1' is equal to the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_EQUAL_TO, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is equal to the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_EQUAL_TO, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is equal to the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$content1' is equal to the specified check value '$conditionValue2' $caseSensitiveMessage."],

            /** CONDITION_LESS_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue2, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, $versionConditionValue2, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent1' is less than the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is less than the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is less than the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_LESS_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is less than the specified check value '".strtoupper($conditionValue1)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is less than the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_LESS_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '".strtoupper($content1)."' is less than the specified check value '$conditionValue1' (case sensitive).", true],
            [VerifyString::CONDITION_LESS_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is less than the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue1' (case sensitive).", true],
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent2' is less than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue2, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent2' is less than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, $versionConditionValue1, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$versionContent1' is less than the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_LESS_THAN, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is less than the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_THAN, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is less than the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],

            /** CONDITION_LESS_EQUAL_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent1' is less than or equal to the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue2, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent1' is less than or equal to the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue2, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $versionConditionValue1, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent1' is less than or equal to the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is less than or equal to the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is less than or equal to the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is less than or equal to the specified check value '".strtoupper($conditionValue1)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is less than or equal to the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '".strtoupper($content1)."' is less than or equal to the specified check value '$conditionValue1' (case sensitive).", true],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is less than or equal to the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $versionConditionValue1, $versionContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$versionContent2' is less than or equal to the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, '', $numericContent2, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$numericContent2' is less than or equal to the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is less than or equal to the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue1' $caseSensitiveMessage."],

            /** CONDITION_GREATER_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is greater than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, $versionConditionValue1, $versionContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent2' is greater than the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is greater than the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is greater than the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_GREATER_THAN, strtoupper($conditionValue1), $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is greater than the specified check value '".strtoupper($conditionValue1)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, strtoupper($conditionValue1), $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is greater than the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_GREATER_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is greater than the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_GREATER_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '".strtoupper($content1)."' is greater than the specified check value '$conditionValue1' (case sensitive).", true],
            [VerifyString::CONDITION_GREATER_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '".strtoupper($content1)."' is greater than the specified check value '$conditionValue1' $caseSensitiveMessage."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue2, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue2, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent2' is greater than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, $versionConditionValue2, $versionContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$versionContent2' is greater than the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_GREATER_THAN, '', $numericContent1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$numericContent1' is greater than the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_THAN, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is greater than the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],

            /** CONDITION_GREATER_EQUAL_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is greater than or equal to the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is greater than or equal to the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue1, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent1' is greater than or equal to the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue2, $versionContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent2' is greater than or equal to the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue1, $versionContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent2' is greater than or equal to the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is greater than or equal to the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is greater than or equal to the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, strtoupper($conditionValue1), $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is greater than or equal to the specified check value '".strtoupper($conditionValue1)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, strtoupper($conditionValue1), $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is greater than or equal to the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is greater than or equal to the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '".strtoupper($content1)."' is greater than or equal to the specified check value '$conditionValue1' (case sensitive).", true],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '".strtoupper($content1)."' is greater than or equal to the specified check value '$conditionValue1' $caseSensitiveMessage."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue2, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$versionContent1' is greater than or equal to the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, '', $numericContent1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is greater than or equal to the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue2' $caseSensitiveMessage."],

            /** CONDITION_DIFFERENT_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue2, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is different than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue2, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is different than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue1, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue2, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$conditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $versionConditionValue2, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$versionContent1' is different than the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is different than the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' is different than the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_DIFFERENT_THAN, strtoupper($conditionValue1), $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is different than the specified check value '".strtoupper($conditionValue1)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, strtoupper($conditionValue1), $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content2' is different than the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_DIFFERENT_THAN, strtoupper($conditionValue1), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' is different than the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '".strtoupper($content1)."' is different than the specified check value '$conditionValue1' (case sensitive).", true],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue1, strtoupper($content1), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '".strtoupper($content1)."' is different than the specified check value '$conditionValue1' $caseSensitiveMessage."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue2, $numericContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent2' is different than the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is different than the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue2, $content2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content2' is different than the specified check value '$conditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $versionConditionValue1, $versionContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$versionContent1' is different than the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_DIFFERENT_THAN, '', $numericContent1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$numericContent1' is different than the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_DIFFERENT_THAN, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' is different than the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue1' $caseSensitiveMessage."],

            /** CONDITION_CONTAINS tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_CONTAINS, $conditionValue1, $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent1' contains the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue1, $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent1' contains the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $conditionValue2, $longContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent2' contains the specified check value '$conditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue2, $longContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent2' contains the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue2, $longContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent2' contains the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $versionConditionValue1, $longContent4, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent4' contains the specified check value '$versionConditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' contains the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' contains the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_CONTAINS, strtoupper($conditionValue1), $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent1' contains the specified check value '".strtoupper($conditionValue1)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, strtoupper($conditionValue1), $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent1' contains the specified check value '".strtoupper($conditionValue1)."' (case sensitive).", true],
            [VerifyString::CONDITION_CONTAINS, strtoupper($conditionValue2), $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent1' contains the specified check value '".strtoupper($conditionValue2)."' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, strtoupper($conditionValue2), $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent1' contains the specified check value '".strtoupper($conditionValue2)."' (case sensitive).", true],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_CONTAINS, $conditionValue2, $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent1' contains the specified check value '$conditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue2, $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent1' contains the specified check value '$numericConditionValue2' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $conditionValue1, $longContent2, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent2' contains the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, $versionConditionValue2, $longContent4, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent4' contains the specified check value '$versionConditionValue2' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_CONTAINS, '', $longContent1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$longContent1' contains the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_CONTAINS, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' contains the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_CONTAINS, $conditionValue2, $longContent1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$longContent1' contains the specified check value '$conditionValue2' $caseSensitiveMessage."],

            /** CONDITION_STARTS_WITH tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_STARTS_WITH, "test", $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' starts with the specified check value 'test' $caseSensitiveMessage."],
            [VerifyString::CONDITION_STARTS_WITH, $conditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' starts with the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_STARTS_WITH, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' starts with the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_STARTS_WITH, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' starts with the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_STARTS_WITH, strtoupper("test"), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' starts with the specified check value 'TEST' $caseSensitiveMessage."],
            [VerifyString::CONDITION_STARTS_WITH, strtoupper("test"), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' starts with the specified check value 'TEST' (case sensitive).", true],
            [VerifyString::CONDITION_STARTS_WITH, strtoupper("xxx"), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' starts with the specified check value 'XXX' $caseSensitiveMessage."],
            [VerifyString::CONDITION_STARTS_WITH, strtoupper("xxx"), $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' starts with the specified check value 'XXX' (case sensitive).", true],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_STARTS_WITH, $numericConditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' starts with the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_STARTS_WITH, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' starts with the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_STARTS_WITH, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' starts with the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_STARTS_WITH, $numericConditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$content1' starts with the specified check value '$numericConditionValue1' $caseSensitiveMessage."],

            /** CONDITION_ENDS_WITH tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_ENDS_WITH, "1", $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' ends with the specified check value '1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_ENDS_WITH, $conditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$content1' ends with the specified check value '$conditionValue1' $caseSensitiveMessage."],
            [VerifyString::CONDITION_ENDS_WITH, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' ends with the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_ENDS_WITH, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' ends with the specified check value '' $caseSensitiveMessage."],
            /** Case-sensitive cases */
            [VerifyString::CONDITION_ENDS_WITH, "TION.", $longContent4, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent4' ends with the specified check value 'TION.' $caseSensitiveMessage."],
            [VerifyString::CONDITION_ENDS_WITH, "TION.", $longContent4, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent4' ends with the specified check value 'TION.' (case sensitive).", true],
            [VerifyString::CONDITION_ENDS_WITH, "XXX", $longContent4, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent4' ends with the specified check value 'XXX' $caseSensitiveMessage."],
            [VerifyString::CONDITION_ENDS_WITH, "XXX", $longContent4, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent4' ends with the specified check value 'XXX' (case sensitive).", true],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_ENDS_WITH, $numericConditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' ends with the specified check value '$numericConditionValue1' $caseSensitiveMessage."],
            /** not successful, fatal */
            [VerifyString::CONDITION_ENDS_WITH, '', $content1, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$content1' ends with the specified check value '' $caseSensitiveMessage."],
            [VerifyString::CONDITION_ENDS_WITH, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' ends with the specified check value '' $caseSensitiveMessage."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_ENDS_WITH, $numericConditionValue1, $content1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$content1' ends with the specified check value '$numericConditionValue1' $caseSensitiveMessage."],

            /** CONDITION_IS_EMPTY tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_IS_EMPTY, "", "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '' is empty."],
            [VerifyString::CONDITION_IS_EMPTY, 'condition-value', "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '' is empty."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_IS_EMPTY, '', $content1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$content1' is empty."],
            [VerifyString::CONDITION_IS_EMPTY, '', $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent1' is empty."],
            [VerifyString::CONDITION_IS_EMPTY, '', $numericContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$numericContent1' is empty."],
            /** not successful, fatal */
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_IS_EMPTY, '', $content1, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$content1' is empty."],

            /** CONDITION_REGEX tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, $longContent1, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Check if the given content '$longContent1' respects the given regex \"$decimalValueRegex\"."],
            [VerifyString::CONDITION_REGEX, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' respects the given regex \"\"."],
            [VerifyString::CONDITION_REGEX, "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' respects the given regex \"\"."],
            /** Wrong condition */
            ['wrong_condition', "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Unsupported condition."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, $longContent3, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '$longContent3' respects the given regex \"$decimalValueRegex\"."],
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Check if the given content '' respects the given regex \"$decimalValueRegex\"."],
            /** not successful, fatal */
            [VerifyString::CONDITION_REGEX, '', $longContent3, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '$longContent3' respects the given regex \"\"."],
            [VerifyString::CONDITION_REGEX, '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Check if the given content '' respects the given regex \"\"."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, $longContent3, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Check if the given content '$longContent3' respects the given regex \"$decimalValueRegex\"."],
        ];
    }

    /**
     * Data provider for actions tests.
     *
     * @return array
     */
    public function actionProvider(): array
    {
        $testSmallVersionString = "1.0.1";
        $testBigVersionString   = "1.1.1";
        $testEmptyString        = "";
        $testString             = "This is the string to be checked by the condition";
        $decimalValueRegex      = '/(\w+)[-+]?([0-9]*\.[0-9]+|[0-9]+)/';
        $wordRegex              = '/[a-z]/';

        return [
            [
                WorkerActionFactory::createVerifyString()->checkContent($testString)->startsWith("This"),
                WorkerActionFactory::createVerifyString()->checkContent($testString)->startsWith("x"),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testString)->endsWith("condition"),
                WorkerActionFactory::createVerifyString()->checkContent($testString)->endsWith("x"),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testString)->contains("to be"),
                WorkerActionFactory::createVerifyString()->checkContent($testString)->contains("x"),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testString)->isEqualTo($testString),
                WorkerActionFactory::createVerifyString()->checkContent($testString)->isEqualTo("x"),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testEmptyString)->isEmpty(),
                WorkerActionFactory::createVerifyString()->checkContent($testString)->isEmpty(),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isLessThan($testBigVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testBigVersionString)->isLessThan($testSmallVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isLessThanEqualTo($testBigVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testBigVersionString)->isLessThanEqualTo($testSmallVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isLessThanEqualTo($testSmallVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testBigVersionString)->isLessThanEqualTo($testSmallVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testBigVersionString)->isGreaterThan($testSmallVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isGreaterThan($testBigVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testBigVersionString)->isGreaterThanEqualTo($testSmallVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isGreaterThanEqualTo($testBigVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isGreaterThanEqualTo($testSmallVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isGreaterThanEqualTo($testBigVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isDifferentThan($testBigVersionString),
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->isDifferentThan($testSmallVersionString),
            ],
            [
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->matchesReqex($decimalValueRegex),
                WorkerActionFactory::createVerifyString()->checkContent($testSmallVersionString)->matchesReqex($wordRegex),
            ],
        ];
    }

    /**
     * Test method FileExists::isValid().
     *
     * @dataProvider conditionsProvider
     *
     * @param string $condition
     * @param $conditionValue
     * @param string $initialContent
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(
        string $condition,
        $conditionValue,
        string $initialContent,
        bool $isValid
    ): void
    {
        if (!$isValid) {
            $this->expectException(ValidationException::class);
        }
        $this->assertEquals(
            $isValid,
            WorkerActionFactory::createVerifyString($condition, $conditionValue, $initialContent)->isValid()
        );
    }

    /**
     * Test for method VerifyString::run().
     *
     * @dataProvider conditionsProvider
     *
     * @param string $condition
     * @param $conditionValue
     * @param string $initialContent
     * @param bool $isValid
     * @param int $actionSeverity
     * @param mixed $expected
     * @param bool $exceptionExpected
     * @param string $objectMessage
     * @param bool $caseSensitive
     *
     * @throws ActionException
     */
    public function testRun(
        string $condition,
        $conditionValue,
        string $initialContent,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $objectMessage,
        bool $caseSensitive = false
    ): void
    {
        $verifyString = WorkerActionFactory::createVerifyString(
            $condition,
            $conditionValue,
            $initialContent
        )->setActionSeverity($actionSeverity);

        if ($caseSensitive) {
            $verifyString->caseSensitive($caseSensitive);
        }

        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $verifyString->run()->getResult());
    }

    /**
     * Test the method VerifyString::stringify().
     *
     * @dataProvider conditionsProvider
     *
     * @param string $condition
     * @param $conditionValue
     * @param string $initialContent
     * @param bool $isValid
     * @param int $actionSeverity
     * @param bool $expected
     * @param bool $exceptionExpected
     * @param string $objectMessage
     * @param bool $caseSensitive
     */
    public function testStringify(
        string $condition,
        $conditionValue,
        string $initialContent,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $objectMessage,
        bool $caseSensitive = false
    ): void
    {
        $this->stringifyTest(
            $objectMessage,
            WorkerActionFactory::createVerifyString($condition, $conditionValue)
                ->checkContent($initialContent)
                ->caseSensitive($caseSensitive)
        );
    }

    /**
     * Test for method VerifyString::checkContent().
     */
    public function testCheckContent(): void
    {
        $verifyString = WorkerActionFactory::createVerifyString(VerifyString::CONDITION_IS_EMPTY, '', 'test1');
        $this->assertInstanceOf(VerifyString::class, $verifyString->checkContent('test2'));
        $this->assertEquals("Check if the given content 'test2' is empty.", (string) $verifyString);
    }

    /**
     * Test the action methods (i.e. contains, startsWith, endsWith, etc).
     *
     * @dataProvider actionProvider
     *
     * @param AbstractAction $verifyMatched
     * @param AbstractAction $verifyNotMatched
     *
     * @throws ActionException
     */
    public function testActions(AbstractAction $verifyMatched, AbstractAction $verifyNotMatched): void
    {
        $this->assertTrue($verifyMatched->run()->getResult());
        $this->assertFalse($verifyNotMatched->run()->getResult());
    }
}
