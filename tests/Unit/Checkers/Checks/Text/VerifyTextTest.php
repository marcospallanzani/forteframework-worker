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

namespace Tests\Unit\Checkers\Checks\Text;

use Forte\Api\Generator\Checkers\Checks\Text\VerifyText;
use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use PHPUnit\Framework\TestCase;

/**
 * Class VerifyTextTest.
 *
 * @package Tests\Unit\Checkers\Checks\Text
 */
class VerifyTextTest extends TestCase
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
            [new VerifyText($content1, VerifyText::CONDITION_EQUAL_TO, $conditionValue1), true, true, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue1'."],
            [new VerifyText($content1, VerifyText::CONDITION_EQUAL_TO, $conditionValue2), true, false, false, "Check if the given content '$content1' is equal to the specified check value '$conditionValue2'."],
            [new VerifyText($content2, VerifyText::CONDITION_EQUAL_TO, $conditionValue1), true, false, false, "Check if the given content '$content2' is equal to the specified check value '$conditionValue1'."],
            [new VerifyText($content2, VerifyText::CONDITION_EQUAL_TO, $conditionValue2), true, true, false, "Check if the given content '$content2' is equal to the specified check value '$conditionValue2'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_EQUAL_TO, $versionConditionValue1), true, true, false, "Check if the given content '$versionContent1' is equal to the specified check value '$versionConditionValue1'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_EQUAL_TO, $versionConditionValue2), true, false, false, "Check if the given content '$versionContent1' is equal to the specified check value '$versionConditionValue2'."],
            [new VerifyText('', VerifyText::CONDITION_EQUAL_TO, ''), false, false, true, "Check if the given content '' is equal to the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_EQUAL_TO, ''), false, false, true, "Check if the given content '$content1' is equal to the specified check value ''."],
            /** CONDITION_LESS_THAN tests */
            [new VerifyText($numericContent1, VerifyText::CONDITION_LESS_THAN, $numericConditionValue1), true, false, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent1, VerifyText::CONDITION_LESS_THAN, $numericConditionValue2), true, true, false, "Check if the given content '$numericContent1' is less than the specified check value '$numericConditionValue2'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_LESS_THAN, $numericConditionValue1), true, false, false, "Check if the given content '$numericContent2' is less than the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_LESS_THAN, $numericConditionValue2), true, false, false, "Check if the given content '$numericContent2' is less than the specified check value '$numericConditionValue2'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_LESS_THAN, $versionConditionValue1), true, false, false, "Check if the given content '$versionContent1' is less than the specified check value '$versionConditionValue1'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_LESS_THAN, $versionConditionValue2), true, true, false, "Check if the given content '$versionContent1' is less than the specified check value '$versionConditionValue2'."],
            [new VerifyText('', VerifyText::CONDITION_LESS_THAN, ''), false, false, true, "Check if the given content '' is less than the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_LESS_THAN, ''), false, false, true, "Check if the given content '$content1' is less than the specified check value ''."],
            /** CONDITION_LESS_EQUAL_THAN tests */
            [new VerifyText($numericContent1, VerifyText::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1), true, true, false, "Check if the given content '$numericContent1' is less than or equal to the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent1, VerifyText::CONDITION_LESS_EQUAL_THAN, $numericConditionValue2), true, true, false, "Check if the given content '$numericContent1' is less than or equal to the specified check value '$numericConditionValue2'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_LESS_EQUAL_THAN, $numericConditionValue1), true, false, false, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_LESS_EQUAL_THAN, $numericConditionValue2), true, true, false, "Check if the given content '$numericContent2' is less than or equal to the specified check value '$numericConditionValue2'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_LESS_EQUAL_THAN, $versionConditionValue1), true, true, false, "Check if the given content '$versionContent1' is less than or equal to the specified check value '$versionConditionValue1'."],
            [new VerifyText($versionContent2, VerifyText::CONDITION_LESS_EQUAL_THAN, $versionConditionValue1), true, false, false, "Check if the given content '$versionContent2' is less than or equal to the specified check value '$versionConditionValue1'."],
            [new VerifyText('', VerifyText::CONDITION_LESS_EQUAL_THAN, ''), false, false, true, "Check if the given content '' is less than or equal to the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_LESS_EQUAL_THAN, ''), false, false, true, "Check if the given content '$content1' is less than or equal to the specified check value ''."],
            /** CONDITION_GREATER_THAN tests */
            [new VerifyText($numericContent1, VerifyText::CONDITION_GREATER_THAN, $numericConditionValue1), true, false, false, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent1, VerifyText::CONDITION_GREATER_THAN, $numericConditionValue2), true, false, false, "Check if the given content '$numericContent1' is greater than the specified check value '$numericConditionValue2'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_GREATER_THAN, $numericConditionValue1), true, true, false, "Check if the given content '$numericContent2' is greater than the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_GREATER_THAN, $numericConditionValue2), true, false, false, "Check if the given content '$numericContent2' is greater than the specified check value '$numericConditionValue2'."],
            [new VerifyText($versionContent2, VerifyText::CONDITION_GREATER_THAN, $versionConditionValue2), true, false, false, "Check if the given content '$versionContent2' is greater than the specified check value '$versionConditionValue2'."],
            [new VerifyText($versionContent2, VerifyText::CONDITION_GREATER_THAN, $versionConditionValue1), true, true, false, "Check if the given content '$versionContent2' is greater than the specified check value '$versionConditionValue1'."],
            [new VerifyText('', VerifyText::CONDITION_GREATER_THAN, ''), false, false, true, "Check if the given content '' is greater than the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_GREATER_THAN, ''), false, false, true, "Check if the given content '$content1' is greater than the specified check value ''."],
            /** CONDITION_GREATER_EQUAL_THAN tests */
            [new VerifyText($numericContent1, VerifyText::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue1), true, true, false, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent1, VerifyText::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2), true, false, false, "Check if the given content '$numericContent1' is greater than or equal to the specified check value '$numericConditionValue2'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue1), true, true, false, "Check if the given content '$numericContent2' is greater than or equal to the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_GREATER_EQUAL_THAN, $numericConditionValue2), true, true, false, "Check if the given content '$numericContent2' is greater than or equal to the specified check value '$numericConditionValue2'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue1), true, true, false, "Check if the given content '$versionContent1' is greater than or equal to the specified check value '$versionConditionValue1'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue2), true, false, false, "Check if the given content '$versionContent1' is greater than or equal to the specified check value '$versionConditionValue2'."],
            [new VerifyText($versionContent2, VerifyText::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue2), true, true, false, "Check if the given content '$versionContent2' is greater than or equal to the specified check value '$versionConditionValue2'."],
            [new VerifyText($versionContent2, VerifyText::CONDITION_GREATER_EQUAL_THAN, $versionConditionValue1), true, true, false, "Check if the given content '$versionContent2' is greater than or equal to the specified check value '$versionConditionValue1'."],
            [new VerifyText('', VerifyText::CONDITION_GREATER_EQUAL_THAN, ''), false, false, true, "Check if the given content '' is greater than or equal to the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_GREATER_EQUAL_THAN, ''), false, false, true, "Check if the given content '$content1' is greater than or equal to the specified check value ''."],
            /** CONDITION_DIFFERENT_THAN tests */
            [new VerifyText($numericContent1, VerifyText::CONDITION_DIFFERENT_THAN, $numericConditionValue1), true, false, false, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent1, VerifyText::CONDITION_DIFFERENT_THAN, $numericConditionValue2), true, true, false, "Check if the given content '$numericContent1' is different than the specified check value '$numericConditionValue2'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_DIFFERENT_THAN, $numericConditionValue1), true, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$numericConditionValue1'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_DIFFERENT_THAN, $numericConditionValue2), true, false, false, "Check if the given content '$numericContent2' is different than the specified check value '$numericConditionValue2'."],
            [new VerifyText($content1, VerifyText::CONDITION_DIFFERENT_THAN, $numericConditionValue1), true, true, false, "Check if the given content '$content1' is different than the specified check value '$numericConditionValue1'."],
            [new VerifyText($content1, VerifyText::CONDITION_DIFFERENT_THAN, $numericConditionValue2), true, true, false, "Check if the given content '$content1' is different than the specified check value '$numericConditionValue2'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_DIFFERENT_THAN, $conditionValue1), true, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$conditionValue1'."],
            [new VerifyText($numericContent2, VerifyText::CONDITION_DIFFERENT_THAN, $conditionValue2), true, true, false, "Check if the given content '$numericContent2' is different than the specified check value '$conditionValue2'."],
            [new VerifyText($content1, VerifyText::CONDITION_DIFFERENT_THAN, $conditionValue1), true, false, false, "Check if the given content '$content1' is different than the specified check value '$conditionValue1'."],
            [new VerifyText($content2, VerifyText::CONDITION_DIFFERENT_THAN, $conditionValue2), true, false, false, "Check if the given content '$content2' is different than the specified check value '$conditionValue2'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_DIFFERENT_THAN, $versionConditionValue2), true, true, false, "Check if the given content '$versionContent1' is different than the specified check value '$versionConditionValue2'."],
            [new VerifyText($versionContent1, VerifyText::CONDITION_DIFFERENT_THAN, $versionConditionValue1), true, false, false, "Check if the given content '$versionContent1' is different than the specified check value '$versionConditionValue1'."],
            [new VerifyText('', VerifyText::CONDITION_DIFFERENT_THAN, ''), false, false, true, "Check if the given content '' is different than the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_DIFFERENT_THAN, ''), false, false, true, "Check if the given content '$content1' is different than the specified check value ''."],
            /** CONDITION_CONTAINS tests */
            [new VerifyText($longContent1, VerifyText::CONDITION_CONTAINS, $conditionValue1), true, true, false, "Check if the given content '$longContent1' contains the specified check value '$conditionValue1'."],
            [new VerifyText($longContent1, VerifyText::CONDITION_CONTAINS, $numericConditionValue1), true, true, false, "Check if the given content '$longContent1' contains the specified check value '$numericConditionValue1'."],
            [new VerifyText($longContent1, VerifyText::CONDITION_CONTAINS, $conditionValue2), true, false, false, "Check if the given content '$longContent1' contains the specified check value '$conditionValue2'."],
            [new VerifyText($longContent1, VerifyText::CONDITION_CONTAINS, $numericConditionValue2), true, false, false, "Check if the given content '$longContent1' contains the specified check value '$numericConditionValue2'."],
            [new VerifyText($longContent2, VerifyText::CONDITION_CONTAINS, $conditionValue2), true, true, false, "Check if the given content '$longContent2' contains the specified check value '$conditionValue2'."],
            [new VerifyText($longContent2, VerifyText::CONDITION_CONTAINS, $numericConditionValue2), true, true, false, "Check if the given content '$longContent2' contains the specified check value '$numericConditionValue2'."],
            [new VerifyText($longContent2, VerifyText::CONDITION_CONTAINS, $conditionValue1), true, false, false, "Check if the given content '$longContent2' contains the specified check value '$conditionValue1'."],
            [new VerifyText($longContent2, VerifyText::CONDITION_CONTAINS, $numericConditionValue2), true, true, false, "Check if the given content '$longContent2' contains the specified check value '$numericConditionValue2'."],
            [new VerifyText($longContent4, VerifyText::CONDITION_CONTAINS, $versionConditionValue1), true, true, false, "Check if the given content '$longContent4' contains the specified check value '$versionConditionValue1'."],
            [new VerifyText($longContent4, VerifyText::CONDITION_CONTAINS, $versionConditionValue2), true, false, false, "Check if the given content '$longContent4' contains the specified check value '$versionConditionValue2'."],
            [new VerifyText('', VerifyText::CONDITION_CONTAINS, ''), false, false, true, "Check if the given content '' contains the specified check value ''."],
            [new VerifyText($content1, VerifyText::CONDITION_CONTAINS, ''), false, false, true, "Check if the given content '$content1' contains the specified check value ''."],
            /** CONDITION_IS_EMPTY tests */
            [new VerifyText($content1, VerifyText::CONDITION_IS_EMPTY), true, false, false, "Check if the given content '$content1' is empty."],
            [new VerifyText($longContent1, VerifyText::CONDITION_IS_EMPTY), true, false, false, "Check if the given content '$longContent1' is empty."],
            [new VerifyText($numericContent1, VerifyText::CONDITION_IS_EMPTY), true, false, false, "Check if the given content '$numericContent1' is empty."],
            [new VerifyText('', VerifyText::CONDITION_IS_EMPTY), true, true, false, "Check if the given content '' is empty."],
            /** CONDITION_REGEX tests */
            [new VerifyText($longContent3, VerifyText::CONDITION_REGEX, $decimalValueRegex), true, false, false, "Check if the given content '$longContent3' respects the given regex \"$decimalValueRegex\"."],
            [new VerifyText($longContent1, VerifyText::CONDITION_REGEX, $decimalValueRegex), true, true, false, "Check if the given content '$longContent1' respects the given regex \"$decimalValueRegex\"."],
            [new VerifyText('', VerifyText::CONDITION_REGEX, $decimalValueRegex), true, false, false, "Check if the given content '' respects the given regex \"$decimalValueRegex\"."],
            [new VerifyText('', VerifyText::CONDITION_REGEX), false, false, true, "Check if the given content '' respects the given regex \"\"."],
            [new VerifyText('', VerifyText::CONDITION_REGEX), false, false, true, "Check if the given content '' respects the given regex \"\"."],
            /** Wrong condition */
            [new VerifyText('', 'wrong_condition'), false, false, true, "Unsupported condition."],
        ];
    }

    /**
     * Test method FileExists::isValid().
     *
     * @dataProvider conditionsProvider
     *
     * @param VerifyText $verifyText
     * @param bool $isValid
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws CheckException
     */
    public function testIsValid(
        VerifyText $verifyText,
        bool $isValid,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(CheckException::class);
        }
        $this->assertEquals($isValid, $verifyText->isValid());
    }

    /**
     * Test for method VerifyText::run().
     *
     * @dataProvider conditionsProvider
     *
     * @param VerifyText $verifyText
     * @param bool $isValid
     * @param bool $expected
     * @param bool $exceptionExpected
     * @param string $objectMessage
     *
     * @throws GeneratorException
     */
    public function testRun(
        VerifyText $verifyText,
        bool $isValid,
        bool $expected,
        bool $exceptionExpected,
        string $objectMessage
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(CheckException::class);
        }
        $this->assertEquals($expected, $verifyText->run());
    }

    /**
     * Test the method VerifyText::stringify().
     *
     * @dataProvider conditionsProvider
     *
     * @param VerifyText $verifyText
     * @param bool $isValid
     * @param bool $expected
     * @param bool $exceptionExpected
     * @param string $objectMessage
     */
    public function testStringify(
        VerifyText $verifyText,
        bool $isValid,
        bool $expected,
        bool $exceptionExpected,
        string $objectMessage
    ): void
    {
        $this->assertEquals($objectMessage, (string) $verifyText);
        $this->assertEquals($objectMessage, $verifyText->stringify());
    }

    /**
     * Test for method VerifyText::setContent().
     */
    public function testSetContent(): void
    {
        $verifyText = new VerifyText('test1', VerifyText::CONDITION_IS_EMPTY);
        $this->assertInstanceOf(VerifyText::class, $verifyText->setContent('test2'));
        $this->assertEquals("Check if the given content 'test2' is empty.", (string) $verifyText);
    }
}
