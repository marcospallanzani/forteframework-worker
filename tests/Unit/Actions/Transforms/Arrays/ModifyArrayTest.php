<?php

namespace Tests\Unit\Actions\Transforms\Arrays;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;
use PHPUnit\Framework\TestCase;

/**
 * Class ModifyArrayTest
 *
 * @package Tests\Unit\Actions\Transforms\Arrays
 */
class ModifyArrayTest extends TestCase
{
    /**
     * Data provider for general modification tests.
     *
     * @return array
     */
    public function modificationsProvider(): array
    {
        return [
            ['key1', ModifyArray::MODIFY_ADD, 'value1', "Add value 'value1' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, ['test-array' => 'array-value'], "Add value '{\"test-array\":\"array-value\"}' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, true, "Add value '1' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, null, "Add value '' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, 'value1', "Modify key 'key1' and set it to 'value1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, ['test-array' => 'array-value'], "Modify key 'key1' and set it to '{\"test-array\":\"array-value\"}'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, true, "Modify key 'key1' and set it to '1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, null, "Modify key 'key1' and set it to ''"],
            ['key1', ModifyArray::MODIFY_REMOVE_KEY, 'value1', "Remove key 'key1'"],
            ['key1', "", 'value1', "Unsupported action"],
        ];
    }

    /**
     * Data provider to test changes on a N-level array. .
     *
     * @return array
     */
    public function complexChangesProvider(): array
    {
        $addArray = ['added' => 'value'];
        $array = ['test1' => ['test3' => ['test4' => ['test5' => 'value5']]], 'test2' => 'value2'];
        $modifiedOneLevel = ['test1' => ['test3' => ['test4' => ['test5' => 'value5']]], 'test2' => 'value2', 'test6' => 'value6'];
        $modifiedArrayThreeLevels = ['test1' => ['test3' => ['test4' => ['key1' => $addArray, 'test5' => 'value5']]], 'test2' => 'value2'];
        $modifiedArrayFourLevels = ['test1' => ['test3' => ['test4' => ['test6' => ['key1' => $addArray], 'test5' => 'value5']]], 'test2' => 'value2'];
        return [
            ['test6', ModifyArray::MODIFY_ADD, 'value6', $array, $modifiedOneLevel],
            ['test1.test3.test4.key1', ModifyArray::MODIFY_ADD, $addArray, $array, $modifiedArrayThreeLevels],
            ['test1.test3.test4.test6.key1', ModifyArray::MODIFY_ADD, $addArray, $array, $modifiedArrayFourLevels],
            ['test6', ModifyArray::MODIFY_CHANGE_VALUE, 'value6', $array, $modifiedOneLevel],
            ['test1.test3.test4.key1', ModifyArray::MODIFY_CHANGE_VALUE, $addArray, $array, $modifiedArrayThreeLevels],
            ['test1.test3.test4.test6.key1', ModifyArray::MODIFY_CHANGE_VALUE, $addArray, $array, $modifiedArrayFourLevels],
            ['test2', ModifyArray::MODIFY_REMOVE_KEY, null, $array, ['test1' => ['test3' => ['test4' => ['test5' => 'value5']]]]],
            ['test1.test3.test4.test5', ModifyArray::MODIFY_REMOVE_KEY, null, $array, ['test1' => ['test3' => ['test4' => []]], 'test2' => 'value2']],
            ['test1.test3.test4', ModifyArray::MODIFY_REMOVE_KEY, null, $array, ['test1' => ['test3' => []], 'test2' => 'value2']],
            ['test1.test3', ModifyArray::MODIFY_REMOVE_KEY, null, $array, ['test1' => [], 'test2' => 'value2']],
            ['test1', ModifyArray::MODIFY_REMOVE_KEY, null, $array, ['test2' => 'value2']],
            ['test-not-found', ModifyArray::MODIFY_REMOVE_KEY, null, $array, $array],
        ];
    }

    /**
     * Data provider for validation tests.
     *
     * @return array
     *
     * @throws ActionException
     */
    public function validationWithErrorsProvider(): array
    {
        return [
            [
                new ModifyArray('key', ModifyArray::MODIFY_ADD),
                '',
                false,
                ['key' => '']
            ],
            [
                $modify = new ModifyArray('', ModifyArray::MODIFY_ADD),
                "No key specified",
                true,
                null
            ],
            [
                $modifyWrongAction = new ModifyArray('key1', 'wrong_action'),
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
                null
            ],
            [
                $emptyModify = new ModifyArray('', ''),
                "No key specified",
                true,
                null
            ],
        ];
    }

    /**
     * Tests the ModifyArray::testStringify() method.
     *
     * @dataProvider modificationsProvider
     *
     * @param string $key
     * @param string $action
     * @param mixed  $value
     * @param string $expected
     */
    public function testStringify(string $key, string $action, $value, string $expected): void
    {
        $modifyArray = new ModifyArray($key, $action, $value);
        $this->assertEquals($expected, $modifyArray->stringify());
        $this->assertEquals($expected, (string) $modifyArray);
        $this->assertEquals($key, $modifyArray->getKey());
        $this->assertEquals($action, $modifyArray->getAction());
        $this->assertEquals($value, $modifyArray->getValue());
    }

    /**
     * Tests all object getters.
     *
     * @dataProvider modificationsProvider
     *
     * @param string $key
     * @param string $action
     * @param mixed  $value
     */
    public function testGetters(string $key, string $action, $value): void
    {
        $modifyArray = new ModifyArray($key, $action, $value);
        $this->assertEquals($key, $modifyArray->getKey());
        $this->assertEquals($action, $modifyArray->getAction());
        $this->assertEquals($value, $modifyArray->getValue());
    }

    /**
     * Tests the run() function.
     *
     * @dataProvider complexChangesProvider
     *
     * @param string $key
     * @param string $action
     * @param mixed $value
     * @param array $array
     * @param array $expected
     *
     * @throws ActionException
     */
    public function testRun(string $key, string $action, $value, array $array, array $expected): void
    {
        $modifyArray = new ModifyArray($key, $action, $value);
        $result = $modifyArray->setModifyContent($array)->run();
        $this->assertTrue($modifyArray->validateResult($result));
        $this->assertEquals($expected, $result->getResult());
    }

    /**
     * Tests the isValid() function.
     *
     * @dataProvider validationWithErrorsProvider
     *
     * @param ModifyArray $modifyArray
     * @param string $exceptionMessage
     * @param bool $expectException
     *
     * @throws ActionException
     */
    public function testIsValidWithErrorMessage(
        ModifyArray $modifyArray,
        string $exceptionMessage,
        bool $expectException
    ): void
    {
        if ($expectException) {
            $this->expectException(ActionException::class);
            $this->expectExceptionMessage($exceptionMessage);
            $isValid = $modifyArray->setModifyContent([])->isValid();
            $this->assertFalse($isValid);
        } else {
            $isValid = $modifyArray->setModifyContent([])->isValid();
            $this->assertTrue($isValid);
        }
    }

    /**
     * Tests the run() function failures.
     *
     * @dataProvider validationWithErrorsProvider
     *
     * @param ModifyArray $modifyArray
     * @param string $exceptionMessage
     * @param bool $expectException
     * @param mixed $expected
     *
     * @throws ActionException
     */
    public function testFailRun(
        ModifyArray $modifyArray,
        string $exceptionMessage,
        bool $expectException,
        $expected
    ): void
    {
        if ($expectException) {
            $this->expectException(ActionException::class);
            $modifyArray->setModifyContent([])->run();
        } else {
            $result = $modifyArray->setModifyContent([])->run();
            $this->assertEquals($expected, $result->getResult());
        }
    }
}