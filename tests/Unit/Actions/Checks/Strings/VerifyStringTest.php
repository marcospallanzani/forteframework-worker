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

namespace Tests\Unit\Actions\Checks\Strings;

use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Tests\Unit\BaseTest;

/**
 * Class VerifyStringTest.
 *
 * @package Tests\Unit\Actions\Checks\Strings
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

        return [
            /** CONDITION_EQUAL_TO tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | validation exception | object message
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue1, $content1, true, false, false, true, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content2, true, false, false, true, false, "Check if the given content '$content2' is equal to the specified check value '$conditionValue2'."],
            [VerifyString::CONDITION_EQUAL_TO, $versionConditionValue1, $versionContent1, true, false, false, true, false, "Check if the given content '$versionContent1' is equal to the specified check value '$versionConditionValue1'."],
            [VerifyString::CONDITION_EQUAL_TO, "", "", false, true, false, false, true, "Check if the given content '' is equal to the specified check value ''."],
            [VerifyString::CONDITION_EQUAL_TO, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is equal to the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content1, true, false, false, false, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue2'."],
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue1, $content2, true, false, false, false, false, "Check if the given content '$content2' is equal to the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_EQUAL_TO, $versionConditionValue2, $versionContent1, true, false, false, false, false, "Check if the given content '$versionContent1' is equal to the specified check value '$versionConditionValue2'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_EQUAL_TO, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is equal to the specified check value ''."],
            [VerifyString::CONDITION_EQUAL_TO, '', '', false, true, false, false, true, "Check if the given content '' is equal to the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_EQUAL_TO, $conditionValue2, $content1, true, false, true, false, true, "Check if the given content '$content1' is equal to the specified check value '$conditionValue2'."],

            /** CONDITION_LESS_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue2, $numericContent1, true, false, false, true, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_LESS_THAN, $versionConditionValue2, $versionContent1, true, false, false, true, false, "Check if the given content '$versionContent1' is less than the specified check value '$versionConditionValue2'."],
            [VerifyString::CONDITION_LESS_THAN, "", "", false, true, false, false, true, "Check if the given content '' is less than the specified check value ''."],
            [VerifyString::CONDITION_LESS_THAN, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is less than the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent1, true, false, false, false, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent2, true, false, false, false, false, "Check if the given content '$numericContent2' is less than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue2, $numericContent2, true, false, false, false, false, "Check if the given content '$numericContent2' is less than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_LESS_THAN, $versionConditionValue1, $versionContent1, true, false, false, false, false, "Check if the given content '$versionContent1' is less than the specified check value '$versionConditionValue1'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_LESS_THAN, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is less than the specified check value ''."],
            [VerifyString::CONDITION_LESS_THAN, '', '', false, true, false, false, true, "Check if the given content '' is less than the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_LESS_THAN, $numericConditionValue1, $numericContent1, true, false, true, false, true, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue1'."],

            /** CONDITION_LESS_EQUAL_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1, $numericContent1, true, false, false, true, false, "Check if the given content '$numericContent1' is less than or equal to the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue2, $numericContent1, true, false, false, true, false, "Check if the given content '$numericContent1' is less than or equal to the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue2, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $versionConditionValue1, $versionContent1, true, false, false, true, false, "Check if the given content '$versionContent1' is less than or equal to the specified check value '$versionConditionValue1'."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, "", "", false, true, false, false, true, "Check if the given content '' is less than or equal to the specified check value ''."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is less than or equal to the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1, $numericContent2, true, false, false, false, false, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $versionConditionValue1, $versionContent2, true, false, false, false, false, "Check if the given content '$versionContent2' is less than or equal to the specified check value '$versionConditionValue1'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, '', $numericContent2, false, true, false, false, true, "Check if the given content '$numericContent2' is less than or equal to the specified check value ''."],
            [VerifyString::CONDITION_LESS_EQUAL_THAN, '', '', false, true, false, false, true, "Check if the given content '' is less than or equal to the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1, $numericContent2, true, false, true, false, true, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue1'."],

            /** CONDITION_GREATER_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue1, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is greater than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_GREATER_THAN, $versionConditionValue1, $versionContent2, true, false, false, true, false, "Check if the given content '$versionContent2' is greater than the specified check value '$versionConditionValue1'."],
            [VerifyString::CONDITION_GREATER_THAN, "", "", false, true, false, false, true, "Check if the given content '' is greater than the specified check value ''."],
            [VerifyString::CONDITION_GREATER_THAN, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is greater than the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue1, $numericContent1, true, false, false, false, false, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue2, $numericContent1, true, false, false, false, false, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue2, $numericContent2, true, false, false, false, false, "Check if the given content '$numericContent2' is greater than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_GREATER_THAN, $versionConditionValue2, $versionContent2, true, false, false, false, false, "Check if the given content '$versionContent2' is greater than the specified check value '$versionConditionValue2'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_GREATER_THAN, '', $numericContent1, false, true, false, false, true, "Check if the given content '$numericContent1' is greater than the specified check value ''."],
            [VerifyString::CONDITION_GREATER_THAN, '', '', false, true, false, false, true, "Check if the given content '' is greater than the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_GREATER_THAN, $numericConditionValue1, $numericContent1, true, false, true, false, true, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue1'."],

            /** CONDITION_GREATER_EQUAL_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue1, $numericContent1, true, false, false, true, false, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue1, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is greater than or equal to the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is greater than or equal to the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue1, $versionContent1, true, false, false, true, false, "Check if the given content '$versionContent1' is greater than or equal to the specified check value '$versionConditionValue1'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue2, $versionContent2, true, false, false, true, false, "Check if the given content '$versionContent2' is greater than or equal to the specified check value '$versionConditionValue2'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue1, $versionContent2, true, false, false, true, false, "Check if the given content '$versionContent2' is greater than or equal to the specified check value '$versionConditionValue1'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, "", "", false, true, false, false, true, "Check if the given content '' is greater than or equal to the specified check value ''."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is greater than or equal to the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2, $numericContent1, true, false, false, false, false, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue2, $versionContent1, true, false, false, false, false, "Check if the given content '$versionContent1' is greater than or equal to the specified check value '$versionConditionValue2'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, '', $numericContent1, false, true, false, false, true, "Check if the given content '$numericContent1' is greater than or equal to the specified check value ''."],
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, '', '', false, true, false, false, true, "Check if the given content '' is greater than or equal to the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2, $numericContent1, true, false, true, false, true, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue2'."],

            /** CONDITION_DIFFERENT_THAN tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue2, $numericContent1, true, false, false, true, false, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $content1, true, false, false, true, false, "Check if the given content '$content1' is different than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue2, $content1, true, false, false, true, false, "Check if the given content '$content1' is different than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue1, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue2, $numericContent2, true, false, false, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$conditionValue2'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $versionConditionValue2, $versionContent1, true, false, false, true, false, "Check if the given content '$versionContent1' is different than the specified check value '$versionConditionValue2'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, "", "", false, true, false, false, true, "Check if the given content '' is different than the specified check value ''."],
            [VerifyString::CONDITION_DIFFERENT_THAN, '', $content1, false, true, false, false, true, "Check if the given content '$content1' is different than the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $numericContent1, true, false, false, false, false, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue2, $numericContent2, true, false, false, false, false, "Check if the given content '$numericContent2' is different than the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue1, $content1, true, false, false, false, false, "Check if the given content '$content1' is different than the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $conditionValue2, $content2, true, false, false, false, false, "Check if the given content '$content2' is different than the specified check value '$conditionValue2'."],
            [VerifyString::CONDITION_DIFFERENT_THAN, $versionConditionValue1, $versionContent1, true, false, false, false, false, "Check if the given content '$versionContent1' is different than the specified check value '$versionConditionValue1'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_DIFFERENT_THAN, '', $numericContent1, false, true, false, false, true, "Check if the given content '$numericContent1' is different than the specified check value ''."],
            [VerifyString::CONDITION_DIFFERENT_THAN, '', '', false, true, false, false, true, "Check if the given content '' is different than the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_DIFFERENT_THAN, $numericConditionValue1, $numericContent1, true, false, true, false, true, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue1'."],

            /** CONDITION_CONTAINS tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_CONTAINS, $conditionValue1, $longContent1, true, false, false, true, false, "Check if the given content '$longContent1' contains the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue1, $longContent1, true, false, false, true, false, "Check if the given content '$longContent1' contains the specified check value '$numericConditionValue1'."],
            [VerifyString::CONDITION_CONTAINS, $conditionValue2, $longContent2, true, false, false, true, false, "Check if the given content '$longContent2' contains the specified check value '$conditionValue2'."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue2, $longContent2, true, false, false, true, false, "Check if the given content '$longContent2' contains the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue2, $longContent2, true, false, false, true, false, "Check if the given content '$longContent2' contains the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_CONTAINS, $versionConditionValue1, $longContent4, true, false, false, true, false, "Check if the given content '$longContent4' contains the specified check value '$versionConditionValue1'."],
            [VerifyString::CONDITION_CONTAINS, "", "", false, true, false, false, true, "Check if the given content '' contains the specified check value ''."],
            [VerifyString::CONDITION_CONTAINS, '', $content1, false, true, false, false, true, "Check if the given content '$content1' contains the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_CONTAINS, $conditionValue2, $longContent1, true, false, false, false, false, "Check if the given content '$longContent1' contains the specified check value '$conditionValue2'."],
            [VerifyString::CONDITION_CONTAINS, $numericConditionValue2, $longContent1, true, false, false, false, false, "Check if the given content '$longContent1' contains the specified check value '$numericConditionValue2'."],
            [VerifyString::CONDITION_CONTAINS, $conditionValue1, $longContent2, true, false, false, false, false, "Check if the given content '$longContent2' contains the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_CONTAINS, $versionConditionValue2, $longContent4, true, false, false, false, false, "Check if the given content '$longContent4' contains the specified check value '$versionConditionValue2'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_CONTAINS, '', $longContent1, false, true, false, false, true, "Check if the given content '$longContent1' contains the specified check value ''."],
            [VerifyString::CONDITION_CONTAINS, '', '', false, true, false, false, true, "Check if the given content '' contains the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_CONTAINS, $conditionValue2, $longContent1, true, false, true, false, true, "Check if the given content '$longContent1' contains the specified check value '$conditionValue2'."],

            /** CONDITION_STARTS_WITH tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_STARTS_WITH, "test", $content1, true, false, false, true, false, "Check if the given content '$content1' starts with the specified check value 'test'."],
            [VerifyString::CONDITION_STARTS_WITH, $conditionValue1, $content1, true, false, false, true, false, "Check if the given content '$content1' starts with the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_STARTS_WITH, "", "", false, true, false, false, true, "Check if the given content '' starts with the specified check value ''."],
            [VerifyString::CONDITION_STARTS_WITH, '', $content1, false, true, false, false, true, "Check if the given content '$content1' starts with the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_STARTS_WITH, $numericConditionValue1, $content1, true, false, false, false, false, "Check if the given content '$content1' starts with the specified check value '$numericConditionValue1'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_STARTS_WITH, '', $content1, false, true, false, false, true, "Check if the given content '$content1' starts with the specified check value ''."],
            [VerifyString::CONDITION_STARTS_WITH, '', '', false, true, false, false, true, "Check if the given content '' starts with the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_STARTS_WITH, $numericConditionValue1, $content1, true, false, true, false, true, "Check if the given content '$content1' starts with the specified check value '$numericConditionValue1'."],

            /** CONDITION_ENDS_WITH tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_ENDS_WITH, "1", $content1, true, false, false, true, false, "Check if the given content '$content1' ends with the specified check value '1'."],
            [VerifyString::CONDITION_ENDS_WITH, $conditionValue1, $content1, true, false, false, true, false, "Check if the given content '$content1' ends with the specified check value '$conditionValue1'."],
            [VerifyString::CONDITION_ENDS_WITH, "", "", false, true, false, false, true, "Check if the given content '' ends with the specified check value ''."],
            [VerifyString::CONDITION_ENDS_WITH, '', $content1, false, true, false, false, true, "Check if the given content '$content1' ends with the specified check value ''."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_ENDS_WITH, $numericConditionValue1, $content1, true, false, false, false, false, "Check if the given content '$content1' ends with the specified check value '$numericConditionValue1'."],
            /** not successful, fatal */
            [VerifyString::CONDITION_ENDS_WITH, '', $content1, false, true, false, false, true, "Check if the given content '$content1' ends with the specified check value ''."],
            [VerifyString::CONDITION_ENDS_WITH, '', '', false, true, false, false, true, "Check if the given content '' ends with the specified check value ''."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_ENDS_WITH, $numericConditionValue1, $content1, true, false, true, false, true, "Check if the given content '$content1' ends with the specified check value '$numericConditionValue1'."],

            /** CONDITION_IS_EMPTY tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_IS_EMPTY, "", "", true, false, false, true, false, "Check if the given content '' is empty."],
            [VerifyString::CONDITION_IS_EMPTY, 'condition-value', "", true, false, false, true, false, "Check if the given content '' is empty."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_IS_EMPTY, '', $content1, true, false, false, false, false, "Check if the given content '$content1' is empty."],
            [VerifyString::CONDITION_IS_EMPTY, '', $longContent1, true, false, false, false, false, "Check if the given content '$longContent1' is empty."],
            [VerifyString::CONDITION_IS_EMPTY, '', $numericContent1, true, false, false, false, false, "Check if the given content '$numericContent1' is empty."],
            /** not successful, fatal */
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_IS_EMPTY, '', $content1, true, false, true, false, true, "Check if the given content '$content1' is empty."],

            /** CONDITION_REGEX tests */
            // Condition | condition value | initial content | is valid | is fatal | is success required | expected | exception | object message
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, $longContent1, true, false, false, true, false, "Check if the given content '$longContent1' respects the given regex \"$decimalValueRegex\"."],
            [VerifyString::CONDITION_REGEX, "", "", false, true, false, false, true, "Check if the given content '' respects the given regex \"\"."],
            [VerifyString::CONDITION_REGEX, "", "", false, true, false, false, true, "Check if the given content '' respects the given regex \"\"."],
            /** Wrong condition */
            ['wrong_condition', "", "", false, true, false, false, true, "Unsupported condition."],
            /** Negative cases */
            /** not successful, no fatal */
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, $longContent3, true, false, false, false, false, "Check if the given content '$longContent3' respects the given regex \"$decimalValueRegex\"."],
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, "", true, false, false, false, false, "Check if the given content '' respects the given regex \"$decimalValueRegex\"."],
            /** not successful, fatal */
            [VerifyString::CONDITION_REGEX, '', $longContent3, false, true, false, false, true, "Check if the given content '$longContent3' respects the given regex \"\"."],
            [VerifyString::CONDITION_REGEX, '', '', false, true, false, false, true, "Check if the given content '' respects the given regex \"\"."],
            /** successful with negative result, is success required */
            [VerifyString::CONDITION_REGEX, $decimalValueRegex, $longContent3, true, false, true, false, true, "Check if the given content '$longContent3' respects the given regex \"$decimalValueRegex\"."],
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
            ActionFactory::createVerifyString($condition, $conditionValue, $initialContent)->isValid()
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
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param mixed $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        string $condition,
        $conditionValue,
        string $initialContent,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected
    ): void
    {
        $verifyString =
            ActionFactory::createVerifyString($condition, $conditionValue, $initialContent)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
        ;
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
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param bool $exceptionExpected
     * @param string $objectMessage
     */
    public function testStringify(
        string $condition,
        $conditionValue,
        string $initialContent,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $objectMessage
    ): void
    {
        $this->stringifyTest(
            $objectMessage,
            ActionFactory::createVerifyString($condition, $conditionValue)->setContent($initialContent)
        );
    }

    /**
     * Test for method VerifyString::setContent().
     */
    public function testSetContent(): void
    {
        $verifyString = ActionFactory::createVerifyString(VerifyString::CONDITION_IS_EMPTY, '', 'test1');
        $this->assertInstanceOf(VerifyString::class, $verifyString->setContent('test2'));
        $this->assertEquals("Check if the given content 'test2' is empty.", (string) $verifyString);
    }
}
