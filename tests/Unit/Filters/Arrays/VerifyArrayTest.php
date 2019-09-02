<?php


namespace Tests\Unit\Filters\Arrays;

use Forte\Worker\Exceptions\GeneratorException;
use Forte\Worker\Exceptions\MissingKeyException;
use Forte\Worker\Filters\Arrays\VerifyArray;
use PHPUnit\Framework\TestCase;

/**
 * Class VerifyArrayTest
 *
 * @package Tests\Unit\Filters\Arrays
 */
class VerifyArrayTest extends TestCase
{
    /**
     * Data provider for general verification tests.
     *
     * @param string $testName
     * @param bool $reverse
     *
     * @return array
     */
    public function verificationsProvider(string $testName, $reverse = false): array
    {
        return [
            ['key1', VerifyArray::CHECK_ANY, 'value1', $reverse, "Check if key 'key1' is set and has any value"],
            ['key1', VerifyArray::CHECK_MISSING_KEY, 'value1', $reverse, "Check if key 'key1' is " . ($reverse ? "" : "not ") . "set"],
            ['key1', VerifyArray::CHECK_ENDS_WITH, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not end" : "ends") . " with value 'value1'"],
            ['key1', VerifyArray::CHECK_STARTS_WITH, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not start" : "starts") . " with value 'value1'"],
            ['key1', VerifyArray::CHECK_EQUALS, 'value1', $reverse, "Check if key 'key1' is set and is " . ($reverse ? "not " : "") . "equal to value 'value1'"],
            ['key1', VerifyArray::CHECK_EMPTY, 'value1', $reverse, "Check if key 'key1' is set and is " . ($reverse ? "not " : "") . "empty (empty string or null)"],
            ['key1', VerifyArray::CHECK_CONTAINS, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value 'value1'"],
            ['key1', VerifyArray::CHECK_CONTAINS, true, $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value '1'"],
            ['key1', VerifyArray::CHECK_CONTAINS, 55, $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value '55'"],
            ['key1', VerifyArray::CHECK_CONTAINS, null, $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value ''"],
            ['key1', VerifyArray::CHECK_CONTAINS, ['test-array' => 'array-value'], $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value '{\"test-array\":\"array-value\"}'"],
            ['key1', "", 'value1', $reverse, "Check if key 'key1' is set"],
        ];
    }

    /**
     * Data provider for general reverse verification tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function reverseVerificationsProvider(string $testName): array
    {
        return $this->verificationsProvider($testName, true);
    }

    /**
     * Data provider for validation tests.
     *
     * @param string $testName
     * @param bool $reverse
     *
     * @return array
     */
    public function validationProvider(string $testName, $reverse = false): array
    {
        $anyException = ($reverse ? true : false);
        return [
            ['key', VerifyArray::CHECK_ANY, '', $reverse, $anyException],
            ['key', VerifyArray::CHECK_ANY, 'value', $reverse, $anyException],
            ['', VerifyArray::CHECK_ANY, '', $reverse, true],
            ['key', VerifyArray::CHECK_MISSING_KEY, '', $reverse, false],
            ['', VerifyArray::CHECK_MISSING_KEY, '', $reverse, true],
            ['', VerifyArray::CHECK_MISSING_KEY, 'value', $reverse, true],
            ['key', VerifyArray::CHECK_ENDS_WITH, '', $reverse, true],
            ['', VerifyArray::CHECK_ENDS_WITH, '', $reverse, true],
            ['', VerifyArray::CHECK_ENDS_WITH, 'value', $reverse, true],
            ['key', VerifyArray::CHECK_ENDS_WITH, 'value', $reverse, false],
            ['key', VerifyArray::CHECK_STARTS_WITH, '', $reverse, true],
            ['', VerifyArray::CHECK_STARTS_WITH, '', $reverse, true],
            ['', VerifyArray::CHECK_STARTS_WITH, 'value', $reverse, true],
            ['key', VerifyArray::CHECK_STARTS_WITH, 'value', $reverse, false],
            ['key', VerifyArray::CHECK_CONTAINS, '', $reverse, true],
            ['', VerifyArray::CHECK_CONTAINS, '', $reverse, true],
            ['', VerifyArray::CHECK_CONTAINS, 'value', $reverse, true],
            ['key', VerifyArray::CHECK_CONTAINS, 'value', $reverse, false],
            ['key', VerifyArray::CHECK_EQUALS, '', $reverse, false],
            ['key', VerifyArray::CHECK_EQUALS, 'value', $reverse, false],
            ['', VerifyArray::CHECK_EQUALS, '', $reverse, true],
            ['', VerifyArray::CHECK_EQUALS, 'value', $reverse, true],
            ['key', VerifyArray::CHECK_EMPTY, '', $reverse, false],
            ['key', VerifyArray::CHECK_EMPTY, 'value', $reverse, false],
            ['', VerifyArray::CHECK_EMPTY, '', $reverse, true],
            ['key1', 'wrong_action', '', $reverse, true],
            ['', '', '', $reverse, true],
            ['key', '', '', $reverse, true],
        ];
    }

    /**
     * Data provider for reverse validation tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function reverseValidationProvider(string $testName): array
    {
        return $this->validationProvider($testName, true);
    }

    /**
     * Data provider for check tests.
     *
     * @param string $testName
     * @param bool $reverse
     *
     * @return array
     */
    public function checkProvider(string $testName, $reverse = false): array
    {
        $array = [
            'test1' => [
                'test2' => 'value2'
            ],
            'test3' => [
                'test4' => 'value4',
                'test14' => [
                    'test15' => 'value15'
                ]
            ],
            'test5' => [
                'test6' => [
                    'test7' => [
                        'test8' => [
                            'test9' => [
                                'test10' => 'this is a long test value'
                            ]
                        ]
                    ],
                    'test11' => [
                        'test12' => [
                            'test13' => 66
                        ]
                    ]
                ]
            ],
            'test16' => [
                'test17' => '',
                'test18' => 'not empty'
            ],
        ];

        $anyException = ($reverse ? true : false);

        return [
            /** CHECK_ANY tests */
            [$array, 'test1.test2', VerifyArray::CHECK_ANY, '', $reverse, true, $anyException],
            [$array, 'test1.test2', VerifyArray::CHECK_ANY, 'value', $reverse, true, $anyException],
            [$array, '', VerifyArray::CHECK_ANY, '', $reverse, false, true],
            /** CHECK_ENDS_WITH tests */
            [$array, '', VerifyArray::CHECK_ENDS_WITH, '', $reverse, false, true],
            [$array, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', $reverse, false, true],
            [$array, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', $reverse, false, true],
            [$array, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', $reverse, true, false],
            [$array, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'lue2', $reverse, true, false],
            [$array, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'value2', $reverse, true, false],
            [$array, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'xxx', $reverse, false, false],
            /** CHECK_STARTS_WITH tests */
            [$array, '', VerifyArray::CHECK_STARTS_WITH, '', $reverse, false, true],
            [$array, '', VerifyArray::CHECK_STARTS_WITH, 'valu', $reverse, false, true],
            [$array, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', $reverse, false, true],
            [$array, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', $reverse, true, false],
            [$array, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value', $reverse, true, false],
            [$array, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value15', $reverse, true, false],
            [$array, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'xxx', $reverse, false, false],
            /** CHECK_CONTAINS tests */
            [$array, '', VerifyArray::CHECK_CONTAINS, '', $reverse, false, true],
            [$array, '', VerifyArray::CHECK_CONTAINS, '55', $reverse, false, true],
            [$array, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', $reverse, false, true],
            [$array, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', $reverse, true, false],
            [$array, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long', $reverse, true, false],
            [$array, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long test value', $reverse, true, false],
            [$array, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'xxx', $reverse, false, false],
            /** CHECK_EQUALS tests */
            [$array, '', VerifyArray::CHECK_EQUALS, '', $reverse, false, true],
            [$array, '', VerifyArray::CHECK_EQUALS, '66', $reverse, false, true],
            [$array, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, $reverse, true, false],
            [$array, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, '66', $reverse, false, false],
            [$array, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, true, $reverse, false, false],
            /** CHECK_EMPTY tests */
            [$array, '', VerifyArray::CHECK_EMPTY, '', $reverse, false, true],
            [$array, '', VerifyArray::CHECK_EMPTY, 'value', $reverse, false, true],
            [$array, 'test16.test17', VerifyArray::CHECK_EMPTY, '', $reverse, true, false],
            [$array, 'test16.test18', VerifyArray::CHECK_EMPTY, '', $reverse, false, false],
            /** CHECK_MISSING_KEY tests */
            [$array, '', VerifyArray::CHECK_MISSING_KEY, '', $reverse, false, true],
            [$array, '', VerifyArray::CHECK_MISSING_KEY, 'value', $reverse, false, true],
            /**
             * Missing key -> key not defined & not in reverse mode -> true
             * Missing key -> key not defined & in reverse mode -> false
             * Missing key -> key defined & not in reverse mode -> true
             * Missing key -> key defined & in reverse mode -> false
             * The above conditions correspond to !$reverse
             */
             [$array, 'test.notdefined', VerifyArray::CHECK_MISSING_KEY, '', $reverse, !$reverse, false],
             [$array, 'test16.test18', VerifyArray::CHECK_MISSING_KEY, '', $reverse, !$reverse, false],
            /** general fail tests */
            [$array, 'key1', 'wrong_action', '', $reverse, false, true],
            [$array, '', '', '', $reverse, false, true],
            [$array, 'key', '', '', $reverse, false, true],
        ];
    }

    /**
     * Data provider for reverse check tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function reverseCheckProvider(string $testName): array
    {
        return $this->checkProvider($testName, true);
    }

    /**
     * Tests the operation message.
     *
     * @dataProvider verificationsProvider
     * @dataProvider reverseVerificationsProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param bool $reverseAction
     * @param string $expected
     */
    public function testOperationMessage(string $key, string $operation, $value, bool $reverseAction, string $expected): void
    {
        $verifyArray = new VerifyArray($key, $operation, $value, $reverseAction);
        $this->assertEquals($expected, $verifyArray->getActionMessage());
        $this->assertEquals($expected, (string) $verifyArray);
    }

    /**
     * Tests all object getters.
     *
     * @dataProvider verificationsProvider
     * @dataProvider reverseVerificationsProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param bool $reverseAction
     */
    public function testGetters(string $key, string $operation, $value, $reverseAction): void
    {
        $modifyArray = new VerifyArray($key, $operation, $value, $reverseAction);
        $this->assertEquals($key, $modifyArray->getKey());
        $this->assertEquals($operation, $modifyArray->getAction());
        $this->assertEquals($value, $modifyArray->getValue());
    }

    /**
     * Test the isValid() function.
     *
     * @dataProvider validationProvider
     * @dataProvider reverseValidationProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed $value
     * @param bool $reverseAction
     * @param bool $expectGeneratorException
     *
     * @throws GeneratorException
     */
    public function testIsValid(
        string $key,
        string $operation,
        $value,
        bool $reverseAction,
        bool $expectGeneratorException
    ): void
    {
        $verifyArray = new VerifyArray($key, $operation, $value, $reverseAction);

        if ($expectGeneratorException) {
            $this->expectException(GeneratorException::class);
            $isValid = $verifyArray->isValid();
            $this->assertFalse($isValid);
        } else {
            $isValid = $verifyArray->isValid();
            $this->assertTrue($isValid);
        }
    }

    /**
     * Test for VerifyArray::checkCondition() function.
     *
     * @dataProvider checkProvider
     * @dataProvider reverseCheckProvider
     *
     * @param array $array
     * @param string $key
     * @param string $operation
     * @param $value
     * @param bool $reverseAction
     * @param bool $expectedResult
     * @param bool $expectException
     *
     * @throws GeneratorException
     */
    public function testCheckCondition(
        array $array,
        string $key,
        string $operation,
        $value,
        bool $reverseAction,
        bool $expectedResult,
        bool $expectException
    ): void
    {
        $verifyArray = new VerifyArray($key, $operation, $value, $reverseAction);

        if ($expectException) {
            $this->expectException(GeneratorException::class);
        }

        $checked = $verifyArray->checkCondition($array);
        if ($reverseAction) {
            $checked = !$checked;
        }
        $this->assertEquals($expectedResult, $checked);
    }

    /**
     * Check if MissingKeyException is thrown when a non-existing key is given
     * to a VerifyArray instance.
     *
     * @throws GeneratorException
     * @throws MissingKeyException
     */
    public function testMissingKey(): void
    {
        $array = [
            "test1" => "test2"
        ];
        $verifyArray = new VerifyArray("missing.key", VerifyArray::CHECK_EQUALS, "value", false);
        $this->expectException(MissingKeyException::class);
        $verifyArray->checkCondition($array);
    }
}