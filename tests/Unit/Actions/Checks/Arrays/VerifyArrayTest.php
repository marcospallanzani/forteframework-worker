<?php

namespace Forte\Worker\Tests\Unit\Actions\Checks\Arrays;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class VerifyArrayTest
 *
 * @package Forte\Worker\Tests\Unit\Actions\Checks\Arrays
 */
class VerifyArrayTest extends BaseTest
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
            ['key1', VerifyArray::CHECK_ENDS_WITH, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not end" : "ends") . " with value 'value1' (case insensitive)"],
            ['key1', VerifyArray::CHECK_ENDS_WITH, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not end" : "ends") . " with value 'value1' (case sensitive)", true],
            ['key1', VerifyArray::CHECK_STARTS_WITH, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not start" : "starts") . " with value 'value1' (case insensitive)"],
            ['key1', VerifyArray::CHECK_STARTS_WITH, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not start" : "starts") . " with value 'value1' (case sensitive)", true],
            ['key1', VerifyArray::CHECK_EQUALS, 'value1', $reverse, "Check if key 'key1' is set and is " . ($reverse ? "not " : "") . "equal to value 'value1' (case insensitive)"],
            ['key1', VerifyArray::CHECK_EQUALS, 'value1', $reverse, "Check if key 'key1' is set and is " . ($reverse ? "not " : "") . "equal to value 'value1' (case sensitive)", true],
            ['key1', VerifyArray::CHECK_EMPTY, 'value1', $reverse, "Check if key 'key1' is set and is " . ($reverse ? "not " : "") . "empty (empty string or null)"],
            ['key1', VerifyArray::CHECK_CONTAINS, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value 'value1' (case insensitive)"],
            ['key1', VerifyArray::CHECK_CONTAINS, 'value1', $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value 'value1' (case sensitive)", true],
            ['key1', VerifyArray::CHECK_CONTAINS, true, $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value 'true'"],
            ['key1', VerifyArray::CHECK_CONTAINS, 55, $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value '55'"],
            ['key1', VerifyArray::CHECK_CONTAINS, null, $reverse, "Check if key 'key1' is set and " . ($reverse ? "does not contain" : "contains") . " value 'null'"],
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
     * @return array
     */
    public function checkProvider(): array
    {
        return [
            // Array to check | key | operation | value | reverse action | severity | expected | exception | case sensitive
            /** CHECK_ANY tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_ENDS_WITH -> in reverse mode -> CHECK_NOT_ENDS_WITH tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'lue2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'value2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'xxx', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'UE2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'UE2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false], /** true because no value is specified */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, null, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true], /** true because no value is specified */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), false, ActionInterface::EXECUTION_SEVERITY_FATAL, null, true],

            /** CHECK_STARTS_WITH -> in reverse mode -> CHECK_NOT_STARTS_WITH tests */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value15', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'xxx', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'VAL', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'VAL', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],  /** true because no value is specified */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, null, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],  /** true because no value is specified */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), false, ActionInterface::EXECUTION_SEVERITY_FATAL, null, true],

            /** CHECK_CONTAINS -> in reverse mode -> CHECK_NOT_CONTAINS tests */
            [$this->testArray, 'test1', VerifyArray::CHECK_CONTAINS, 'test2', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long test value', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'xxx', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'LONG', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'LONG', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false], /** true because no value is specified */
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, null, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true], /** true because no value is specified */
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), false, ActionInterface::EXECUTION_SEVERITY_FATAL, null, true],

            /** CHECK_EQUALS -> in reverse mode -> CHECK_NOT_EQUALS tests */
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, '66', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, true, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_EQUALS, 'THIS IS A LONG TEST VALUE', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_EQUALS, 'THIS IS A LONG TEST VALUE', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, true, true],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_EMPTY -> in reverse mode -> CHECK_NOT_EMPTY tests */
            [$this->testArray, 'test16.test17', VerifyArray::CHECK_EMPTY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_EMPTY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_MISSING_KEY -> in reverse mode -> CHECK_NOT_MISSING_KEY tests */
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_MISSING_KEY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test.notdefined', VerifyArray::CHECK_MISSING_KEY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** general fail tests */
            /** not successful, no fatal */
            [$this->testArray, 'key1', 'wrong_action', '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, null, false],
            [$this->testArray, '', '', '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, null, false],
            [$this->testArray, 'key', '', '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, null, false],
            /** not successful, fatal */
            [$this->testArray, 'key1', 'wrong_action', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, null, true],
            [$this->testArray, '', '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, null, true],
            [$this->testArray, 'key', '', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, null, true],
//TODO MISSING TESTS FOR SUCCESS REQUIRED AND CRITICAL CASES
        ];
    }

    /**
     * Data provider for check reverse tests.
     *
     * @return array
     */
    public function checkReverseProvider(): array
    {
        return [
            // Array to check | key | operation | value | reverse action | is fatal | is success required | expected | exception | case sensitive
            /** CHECK_ANY tests */
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_ENDS_WITH -> in reverse mode -> CHECK_NOT_ENDS_WITH tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'lue2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'value2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'xxx', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'XXX', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'LUE2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'LUE2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_STARTS_WITH -> in reverse mode -> CHECK_NOT_STARTS_WITH tests */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value15', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'xxx', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_STARTS_WITH, 'XXX', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_STARTS_WITH, 'VAL', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_STARTS_WITH, 'VAL', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_CONTAINS -> in reverse mode -> CHECK_NOT_CONTAINS tests */
            [$this->testArray, 'test1', VerifyArray::CHECK_CONTAINS, 'test2', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long test value', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'xxx', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'A LONG TEST', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'A LONG TEST', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'XXX', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_EQUALS -> in reverse mode -> CHECK_NOT_EQUALS tests */
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false /*they are equal*/, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, '66', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false /*they are equal*/, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, true, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Case-sensitive cases */
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false /*they are equal*/, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_EQUALS, 'THIS IS A LONG TEST VALUE', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_EQUALS, 'THIS IS A LONG TEST VALUE', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_EMPTY -> in reverse mode -> CHECK_NOT_EMPTY tests */
            [$this->testArray, 'test16.test17', VerifyArray::CHECK_EMPTY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_EMPTY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** CHECK_MISSING_KEY -> in reverse mode -> CHECK_NOT_MISSING_KEY tests */
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_MISSING_KEY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$this->testArray, 'test.notdefined', VerifyArray::CHECK_MISSING_KEY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** general fail tests */
            /** not successful, no fatal */
            [$this->testArray, 'key1', 'wrong_action', '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, '', '', '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$this->testArray, 'key', '', '', true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [$this->testArray, 'key1', 'wrong_action', '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, '', '', '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [$this->testArray, 'key', '', '', true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
        ];
//TODO MISSING TEST CASES FOR SUCCESS REQUIRED AND CRITICAL SEVERITIES
    }

    /**
     * Data provider for actions tests.
     *
     * @return array
     */
    public function actionProvider(): array
    {
        $testArray     = ['key1' => 'value1', 'key2' => ''];
        $keyWithValue  = "key1";
        $keyEmptyValue = "key2";

        return [
            [
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->startsWith($keyWithValue, 'val'),
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->startsWith($keyWithValue, 'x'),
            ],
            [
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->endsWith($keyWithValue, 'ue1'),
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->endsWith($keyWithValue, 'x'),
            ],
            [
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->contains($keyWithValue, 'ue'),
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->contains($keyWithValue, 'x'),
            ],
            [
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->isEqualTo($keyWithValue, 'value1'),
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->isEqualTo($keyWithValue, 'x'),
            ],
            [
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->isEmpty($keyEmptyValue),
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->isEmpty($keyWithValue),
            ],
            [
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->isKeyMissing('key3'),
                WorkerActionFactory::createVerifyArray()->checkContent($testArray)->isKeyMissing($keyWithValue),
            ],
        ];
    }

    /**
     * Tests the operation message.
     *
     * @dataProvider verificationsProvider
     * @dataProvider reverseVerificationsProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed $value
     * @param bool $reverseAction
     * @param string $expected
     * @param bool $caseSensitive
     */
    public function testStringify(
        string $key,
        string $operation,
        $value,
        bool $reverseAction,
        string $expected,
        bool $caseSensitive = false
    ): void
    {
        $this->stringifyTest(
            $expected,
            WorkerActionFactory::createVerifyArray($key, $operation, $value, $reverseAction, $caseSensitive)
        );
    }

    /**
     * Test the isValid() function.
     *
     * @dataProvider validationProvider
     * @dataProvider reverseValidationProvider
     *
     * @param array $checkContent
     * @param string $key
     * @param string $operation
     * @param mixed $value
     * @param bool $reverseAction
     * @param bool $expectException
     *
     * @throws ValidationException
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
        $verifyArray = WorkerActionFactory::createVerifyArray($key, $operation, $value, $reverseAction);
        if ($expectException) {
            $this->expectException(ValidationException::class);
            $isValid = $verifyArray->checkContent($checkContent)->isValid();
            $this->assertFalse($isValid);
        } else {
            $isValid = $verifyArray->checkContent($checkContent)->isValid();
            $this->assertTrue($isValid);
        }
    }

    /**
     * Test for VerifyArray::run() function.
     *
     * @dataProvider checkProvider
     * @dataProvider checkReverseProvider
     *
     * @param array $array
     * @param string $key
     * @param string $operation
     * @param $value
     * @param bool $reverseAction
     * @param int $actionSeverity
     * @param mixed $expectedResult
     * @param bool $expectException
     * @param bool $caseSensitive
     *
     * @throws ActionException
     */
    public function testRun(
        array $array,
        string $key,
        string $operation,
        $value,
        bool $reverseAction,
        int $actionSeverity,
        $expectedResult,
        bool $expectException,
        bool $caseSensitive = false
    ): void
    {
        /** @var VerifyArray $verifyArray */
        $verifyArray =
            WorkerActionFactory::createVerifyArray($key, $operation, $value, $reverseAction)
                ->checkContent($array)
                ->caseSensitive($caseSensitive)
                ->setActionSeverity($actionSeverity)
        ;

        if ($expectException) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expectedResult, $verifyArray->run()->getResult());
    }

    /**
     * Check if MissingKeyException is thrown when a non-existing key is given
     * to a VerifyArray instance.
     *
     * @throws WorkerException
     */
    public function testMissingKeyException(): void
    {
        $array = [
            "test1" => "test2"
        ];
        $verifyArray = WorkerActionFactory::createVerifyArray("missing.key", VerifyArray::CHECK_EQUALS, "value", false);
        $verifyArray->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL);
        $this->expectException(ActionException::class);
        $verifyArray->checkContent($array)->run();
    }

    /**
     * Check if MissingKeyException is thrown when a non-existing key is given
     * to a VerifyArray instance.
     *
     * @throws WorkerException
     */
    public function testMissingKeyNegativeResult(): void
    {
        $array = [
            "test1" => "test2"
        ];
        $verifyArray = WorkerActionFactory::createVerifyArray("missing.key", VerifyArray::CHECK_EQUALS, "value", false);
        $actionResult = $verifyArray->checkContent($array)->run();
        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $this->assertEmpty($actionResult->getResult());
        $this->assertCount(1, $actionResult->getActionFailures());
        $mainFailure = current($actionResult->getActionFailures());
        $this->assertInstanceOf(ActionException::class, $mainFailure);
        $this->assertEquals("Array key 'missing.key' not found.", $mainFailure->getMessage());

    }

    /**
     * Test the VerifyArray::apply() method, if a wrong action is defined.
     */
    public function testRunWithWrongType(): void
    {
        $this->expectException(ValidationException::class);
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
        $verifyArrayMock->checkContent($this->testArray)->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL);
        $verifyArrayMock->run();
    }

    /**
     * Test the action methods (i.e. contains, startsWith, endsWith, etc).
     * It also checks the reverse mode.
     *
     * Starts with -> does not start with
     * Ends with -> does not end with
     * Contains -> does not contain
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

        // Check the action in reverse mode
        $this->assertFalse($verifyMatched->reverse()->run()->getResult());
        $this->assertTrue($verifyNotMatched->reverse()->run()->getResult());
    }
}
