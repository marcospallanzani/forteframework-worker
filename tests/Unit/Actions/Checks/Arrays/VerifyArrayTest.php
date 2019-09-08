<?php

namespace Tests\Unit\Actions\Checks\Arrays;

use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Tests\Unit\BaseTest;

/**
 * Class VerifyArrayTest
 *
 * @package Tests\Unit\Actions\Checks\Arrays
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
     * @return array
     */
    public function checkProvider(): array
    {
        return [
            // Array to check | key | operation | value | reverse action | is fatal | is success required | expected | exception
            /** CHECK_ANY tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', false, false, false, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', false, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', false, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', false, true, false, false, true],

            /** CHECK_ENDS_WITH -> in reverse mode -> CHECK_NOT_ENDS_WITH tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, false, false, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'lue2', false, false, false, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'value2', false, false, false, true, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'xxx', false, false, false, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', false, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', false, false, false, false, false], /** true because no value is specified */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), false, false, false, null, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', false, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', false, true, false, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', false, true, false, false, true], /** true because no value is specified */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), false, true, false, null, true],

            /** CHECK_STARTS_WITH -> in reverse mode -> CHECK_NOT_STARTS_WITH tests */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', false, false, false, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value', false, false, false, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value15', false, false, false, true, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'xxx', false, false, false, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', false, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', false, false, false, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', false, false, false, false, false],  /** true because no value is specified */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), false, false, false, null, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', false, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', false, true, false, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', false, true, false, false, true],  /** true because no value is specified */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), false, true, false, null, true],

            /** CHECK_CONTAINS -> in reverse mode -> CHECK_NOT_CONTAINS tests */
            [$this->testArray, 'test1', VerifyArray::CHECK_CONTAINS, 'test2', false, false, false, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', false, false, false, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long', false, false, false, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long test value', false, false, false, true, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'xxx', false, false, false, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', false, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', false, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', false, false, false, false, false], /** true because no value is specified */
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), false, false, false, null, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', false, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', false, true, false, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', false, true, false, false, true], /** true because no value is specified */
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), false, true, false, null, true],

            /** CHECK_EQUALS -> in reverse mode -> CHECK_NOT_EQUALS tests */
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, false, false, false, true, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, '66', false, false, false, true, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, true, false, false, false, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', false, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', false, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', false, true, false, true, true],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', false, true, false, false, true],

            /** CHECK_EMPTY -> in reverse mode -> CHECK_NOT_EMPTY tests */
            [$this->testArray, 'test16.test17', VerifyArray::CHECK_EMPTY, '', false, false, false, true, false],
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_EMPTY, '', false, false, false, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', false, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', false, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', false, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', false, true, false, false, true],

            /** CHECK_MISSING_KEY -> in reverse mode -> CHECK_NOT_MISSING_KEY tests */
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_MISSING_KEY, '', false, false, false, false, false],
            [$this->testArray, 'test.notdefined', VerifyArray::CHECK_MISSING_KEY, '', false, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', false, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', false, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', false, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', false, true, false, false, true],

            /** general fail tests */
            /** not successful, no fatal */
            [$this->testArray, 'key1', 'wrong_action', '', false, false, false, null, false],
            [$this->testArray, '', '', '', false, false, false, null, false],
            [$this->testArray, 'key', '', '', false, false, false, null, false],
            /** not successful, fatal */
            [$this->testArray, 'key1', 'wrong_action', '', false, true, false, null, true],
            [$this->testArray, '', '', '', false, true, false, null, true],
            [$this->testArray, 'key', '', '', false, true, false, null, true],
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
            // Array to check | key | operation | value | reverse action | is fatal | is success required | expected | exception
            /** CHECK_ANY tests */
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', true, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, '', true, true, false, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ANY, 'value', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ANY, '', true, true, false, false, true],

            /** CHECK_ENDS_WITH -> in reverse mode -> CHECK_NOT_ENDS_WITH tests */
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'ue2', true, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'lue2', true, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'value2', true, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, 'xxx', true, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', true, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', true, false, false, false, false],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, '', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_ENDS_WITH, 'ue2', true, true, false, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, '', true, true, false, false, true],
            [$this->testArray, 'test1.test2', VerifyArray::CHECK_ENDS_WITH, new \stdClass(), true, true, false, false, true],

            /** CHECK_STARTS_WITH -> in reverse mode -> CHECK_NOT_STARTS_WITH tests */
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'val', true, false, false, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value', true, false, false, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'value15', true, false, false, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, 'xxx', true, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', true, false, false, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', true, false, false, false, false],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, '', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_STARTS_WITH, 'valu', true, true, false, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, '', true, true, false, false, true],
            [$this->testArray, 'test3.test14.test15', VerifyArray::CHECK_STARTS_WITH, new \stdClass(), true, true, false, false, true],

            /** CHECK_CONTAINS -> in reverse mode -> CHECK_NOT_CONTAINS tests */
            [$this->testArray, 'test1', VerifyArray::CHECK_CONTAINS, 'test2', true, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'long', true, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long', true, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'a long test value', true, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, 'xxx', true, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', true, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', true, false, false, false, false],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_CONTAINS, '55', true, true, false, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, '', true, true, false, false, true],
            [$this->testArray, 'test5.test6.test7.test8.test9.test10', VerifyArray::CHECK_CONTAINS, new \stdClass(), true, true, false, false, true],

            /** CHECK_EQUALS -> in reverse mode -> CHECK_NOT_EQUALS tests */
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, 66, true, false, false, false /*they are equal*/, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, '66', true, false, false, false /*they are equal*/, false],
            [$this->testArray, 'test5.test6.test11.test12.test13', VerifyArray::CHECK_EQUALS, true, true, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EQUALS, '66', true, true, false, false, true],

            /** CHECK_EMPTY -> in reverse mode -> CHECK_NOT_EMPTY tests */
            [$this->testArray, 'test16.test17', VerifyArray::CHECK_EMPTY, '', true, false, false, false, false],
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_EMPTY, '', true, false, false, true, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, '', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_EMPTY, 'value', true, true, false, false, true],

            /** CHECK_MISSING_KEY -> in reverse mode -> CHECK_NOT_MISSING_KEY tests */
            [$this->testArray, 'test16.test18', VerifyArray::CHECK_MISSING_KEY, '', true, false, false, true, false],
            [$this->testArray, 'test.notdefined', VerifyArray::CHECK_MISSING_KEY, '', true, false, false, false, false],
            /** negative cases */
            /** not successful, no fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', true, false, false, false, false],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, '', true, true, false, false, true],
            [$this->testArray, '', VerifyArray::CHECK_MISSING_KEY, 'value', true, true, false, false, true],

            /** general fail tests */
            /** not successful, no fatal */
            [$this->testArray, 'key1', 'wrong_action', '', true, false, false, false, false],
            [$this->testArray, '', '', '', true, false, false, false, false],
            [$this->testArray, 'key', '', '', true, false, false, false, false],
            /** not successful, fatal */
            [$this->testArray, 'key1', 'wrong_action', '', true, true, false, false, true],
            [$this->testArray, '', '', '', true, true, false, false, true],
            [$this->testArray, 'key', '', '', true, true, false, false, true],
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
        $verifyArray = new VerifyArray($key, $operation, $value, $reverseAction);
        if ($expectException) {
            $this->expectException(ValidationException::class);
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
     * @dataProvider checkReverseProvider
     *
     * @param array $array
     * @param string $key
     * @param string $operation
     * @param $value
     * @param bool $reverseAction
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param mixed $expectedResult
     * @param bool $expectException
     *
     */
    public function testRun(
        array $array,
        string $key,
        string $operation,
        $value,
        bool $reverseAction,
        bool $isFatal,
        bool $isSuccessRequired,
        $expectedResult,
        bool $expectException
    ): void
    {
        $verifyArray =
            (new VerifyArray($key, $operation, $value, $reverseAction))
            ->setIsFatal($isFatal)
            ->setIsSuccessRequired($isSuccessRequired)
        ;
        if ($expectException) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expectedResult, $verifyArray->setCheckContent($array)->run()->getResult());
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
        $verifyArray = new VerifyArray("missing.key", VerifyArray::CHECK_EQUALS, "value", false);
        $verifyArray->setIsFatal(true);
        $this->expectException(ActionException::class);
        $verifyArray->setCheckContent($array)->run();
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
        $verifyArray = new VerifyArray("missing.key", VerifyArray::CHECK_EQUALS, "value", false);
        $actionResult = $verifyArray->setCheckContent($array)->run();
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
        $verifyArrayMock->setCheckContent($this->testArray)->setIsFatal(true);
        $verifyArrayMock->run();
    }
}
