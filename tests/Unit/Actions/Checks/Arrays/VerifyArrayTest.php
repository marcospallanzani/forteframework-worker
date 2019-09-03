<?php

namespace Tests\Unit\Actions\Checks\Arrays;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use PHPUnit\Framework\TestCase;

/**
 * Class VerifyArrayTest
 *
 * @package Tests\Unit\Actions\Checks\Arrays
 */
class VerifyArrayTest extends TestCase
{
    /**
     * A general test array.
     *
     * @var array
     */
    protected $testArray = [
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
            [$this->testArray, 'key', VerifyArray::CHECK_ANY, '', $reverse, $anyException],
            [$this->testArray, 'key', VerifyArray::CHECK_ANY, 'value', $reverse, $anyException],
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', $reverse, true],
            [$this->testArray, 'key', VerifyArray::CHECK_MISSING_KEY, '', $reverse, false],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', $reverse, true],
            [$this->testArray, 'key', VerifyArray::CHECK_ENDS_WITH, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'value', $reverse, true],
            [$this->testArray, 'key', VerifyArray::CHECK_ENDS_WITH, 'value', $reverse, false],
            [$this->testArray, 'key', VerifyArray::CHECK_STARTS_WITH, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'value', $reverse, true],
            [$this->testArray, 'key', VerifyArray::CHECK_STARTS_WITH, 'value', $reverse, false],
            [$this->testArray, 'key', VerifyArray::CHECK_CONTAINS, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, 'value', $reverse, true],
            [$this->testArray, 'key', VerifyArray::CHECK_CONTAINS, 'value', $reverse, false],
            [$this->testArray, 'key', VerifyArray::CHECK_EQUALS, '', $reverse, false],
            [$this->testArray, 'key', VerifyArray::CHECK_EQUALS, 'value', $reverse, false],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', $reverse, true],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, 'value', $reverse, true],
            [$this->testArray, 'key', VerifyArray::CHECK_EMPTY, '', $reverse, false],
            [$this->testArray, 'key', VerifyArray::CHECK_EMPTY, 'value', $reverse, false],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', $reverse, true],
            [$this->testArray, 'key1', 'wrong_action', '', $reverse, true],
            [$this->testArray, '', '', '', $reverse, true],
            [$this->testArray, 'key', '', '', $reverse, true],
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
        $anyException = ($reverse ? true : false);

        return [
            // Array to check | key | operation | value | reverse action | expected | exception
            /** CHECK_ANY tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', $reverse, true, $anyException],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', $reverse, true, $anyException],
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', $reverse, false, true],
            /** CHECK_ENDS_WITH tests */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', $reverse, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', $reverse, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', $reverse, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', $reverse, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'lue2', $reverse, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'value2', $reverse, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'xxx', $reverse, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), $reverse, false, true],
            /** CHECK_STARTS_WITH tests */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', $reverse, false, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', $reverse, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', $reverse, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', $reverse, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value', $reverse, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value15', $reverse, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'xxx', $reverse, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), $reverse, false, true],
            /** CHECK_CONTAINS tests */
            [$this->testArray, 'test1', VerifyArray::CHECK_CONTAINS, 'test2', $reverse, true, false],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', $reverse, false, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', $reverse, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', $reverse, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', $reverse, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long', $reverse, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long test value', $reverse, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'xxx', $reverse, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), $reverse, false, true],
            /** CHECK_EQUALS tests */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', $reverse, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', $reverse, false, true],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, $reverse, true, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, '66', $reverse, false, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, true, $reverse, false, false],
            /** CHECK_EMPTY tests */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', $reverse, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', $reverse, false, true],
            [$this->testArray, 'test16.test17', VerifyArray::CHECK_EMPTY, '', $reverse, true, false],
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_EMPTY, '', $reverse, false, false],
            /** CHECK_MISSING_KEY tests */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', $reverse, false, true],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', $reverse, false, true],
            /**
             * Missing key -> key not defined & not in reverse mode -> true
             * Missing key -> key not defined & in reverse mode -> false
             * Missing key -> key defined & not in reverse mode -> true
             * Missing key -> key defined & in reverse mode -> false
             * The above conditions correspond to !$reverse
             */
             [$this->testArray, 'test.notdefined', VerifyArray::CHECK_MISSING_KEY, '', $reverse, !$reverse, false],
             [$this->testArray, 'test16.test18', VerifyArray::CHECK_MISSING_KEY, '', $reverse, !$reverse, false],
            /** general fail tests */
            [$this->testArray, 'key1', 'wrong_action', '', $reverse, false, true],
            [$this->testArray, '', '', '', $reverse, false, true],
            [$this->testArray, 'key', '', '', $reverse, false, true],
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
    public function testStringify(string $key, string $operation, $value, bool $reverseAction, string $expected): void
    {
        $verifyArray = new VerifyArray($key, $operation, $value, $reverseAction);
        $this->assertEquals($expected, $verifyArray->stringify());
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
        $this->assertEquals($reverseAction, $modifyArray->getReverseAction());
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
     * @param bool $expectException
     *
     * @throws WorkerException
     */
    public function testIsValid(
        array $checkContent,
        string $key,
        string $operation,
        $value,
        bool $reverseAction,
        bool $expectException
    ): void
    {
        $verifyArray = new VerifyArray($key, $operation, $value, $reverseAction);

        if ($expectException) {
            $this->expectException(ActionException::class);
            $isValid = $verifyArray->setCheckContent($checkContent)->isValid();
            $this->assertFalse($isValid);
        } else {
            $isValid = $verifyArray->setCheckContent($checkContent)->isValid();
            $this->assertTrue($isValid);
        }
    }

    /**
     * Test for VerifyArray::run() function.
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
     * @throws WorkerException
     */
    public function testRun(
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
            $this->expectException(ActionException::class);
        }

        $checked = $verifyArray->setCheckContent($array)->run();
        if ($reverseAction) {
            $checked = !$checked;
        }
        $this->assertEquals($expectedResult, $checked);
    }

    /**
     * Check if MissingKeyException is thrown when a non-existing key is given
     * to a VerifyArray instance.
     *
     * @throws WorkerException
     */
    public function testMissingKey(): void
    {
        $array = [
            "test1" => "test2"
        ];
        $verifyArray = new VerifyArray("missing.key", VerifyArray::CHECK_EQUALS, "value", false);
        $this->expectException(ActionException::class);
        $verifyArray->setCheckContent($array)->run();
    }

    /**
     * Test the VerifyArray::apply() method, if a wrong action is defined.
     */
    public function testRunWithWrongType(): void
    {
        $this->expectException(WorkerException::class);
        $verifyArrayMock = \Mockery::mock(VerifyArray::class, array('test1', 'wrong-action'))
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
        ;
        $verifyArrayMock
            ->shouldReceive('isValid')
            ->once()
            ->andReturn(true)
        ;
        $verifyArrayMock
            ->shouldReceive('runBeforeActions')
            ->once()
            ->andReturn([])
        ;
        $verifyArrayMock
            ->shouldReceive('runAfterActions')
            ->once()
            ->andReturn([])
        ;
        $verifyArrayMock->setCheckContent($this->testArray);
        $verifyArrayMock->run();
    }
}