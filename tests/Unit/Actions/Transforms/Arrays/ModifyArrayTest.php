<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Arrays;

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ModifyArrayTest
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Arrays
 */
class ModifyArrayTest extends BaseTest
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
            ['key1', ModifyArray::MODIFY_ADD, true, "Add value 'true' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, null, "Add value 'null' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, 'value1', "Modify key 'key1' and set it to 'value1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, ['test-array' => 'array-value'], "Modify key 'key1' and set it to '{\"test-array\":\"array-value\"}'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, true, "Modify key 'key1' and set it to 'true'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, null, "Modify key 'key1' and set it to 'null'"],
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
     */
    public function validationWithErrorsProvider(): array
    {
        // Action | exception message | expect exception | expected value
        return [
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_ADD),
                '',
                false,
                ['key' => '']
            ],
            [
                ActionFactory::createModifyArray('', ModifyArray::MODIFY_ADD),
                "No key specified",
                true,
                null
            ],
            [
                $modifyWrongAction = ActionFactory::createModifyArray('key1', 'wrong_action'),
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
                null
            ],
            [
                ActionFactory::createModifyArray('', ''),
                "No key specified",
                true,
                null
            ],
        ];
    }

    /**
     * Data provider for run tests.
     *
     * @return array
     */
    public function runWithErrorsProvider(): array
    {
        // Action | exception message | expect exception | expected value | fatal | success required
        return [
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_ADD),
                '',
                false,
                ['key' => '']
            ],
            [
                ActionFactory::createModifyArray('', ModifyArray::MODIFY_ADD),
                "No key specified",
                true,
                null,
                true,
                false
            ],
            [
                ActionFactory::createModifyArray('', ModifyArray::MODIFY_ADD),
                "No key specified",
                true,
                null,
                false,
                true
            ],
            [
                ActionFactory::createModifyArray('', ''),
                "No key specified",
                true,
                null,
                true,
                false
            ],
            [
                ActionFactory::createModifyArray('', ''),
                "No key specified",
                true,
                null,
                false,
                true
            ],
            [
                $modifyWrongAction = ActionFactory::createModifyArray('key1', 'wrong_action'),
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
                null,
                true,
                false
            ],
            [
                $modifyWrongAction = ActionFactory::createModifyArray('key1', 'wrong_action'),
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
                null,
                false,
                true
            ],
        ];
    }

    /**
     * Data provider for actions tests.
     *
     * @return array
     */
    public function actionProvider(): array
    {
        $testArray = ['key1' => 'value1', 'key2' => ''];

        return [
            [
                ActionFactory::createModifyArray()->addKey('key3', 'value3'),
                $testArray,
                array_merge($testArray, ['key3' => 'value3'])
            ],
            [
                ActionFactory::createModifyArray()->addKey('key2', 'value2'),
                $testArray,
                array_merge($testArray, ['key2' => 'value2'])
            ],
            [
                ActionFactory::createModifyArray()->modifyKey('key3', 'value3'),
                $testArray,
                array_merge($testArray, ['key3' => 'value3'])
            ],
            [
                ActionFactory::createModifyArray()->modifyKey('key2', 'value2'),
                $testArray,
                array_merge($testArray, ['key2' => 'value2'])
            ],
            [
                ActionFactory::createModifyArray()->removeKey('key2'),
                $testArray,
                ['key1' => 'value1']
            ],
            [
                ActionFactory::createModifyArray()->removeKey('key4'),
                $testArray,
                $testArray
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
        $this->stringifyTest($expected, ActionFactory::createModifyArray($key, $action, $value));
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
        $this->runBasicTest(
            false,
            true,
            ActionFactory::createModifyArray($key, $action, $value)->modifyContent($array),
            $expected
        );
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
     * @throws ValidationException
     */
    public function testIsValidWithErrorMessage(
        ModifyArray $modifyArray,
        string $exceptionMessage,
        bool $expectException
    ): void
    {
        $this->isValidTest(!$expectException, $modifyArray, $exceptionMessage);
    }

    /**
     * Tests the run() function failures.
     *
     * @dataProvider runWithErrorsProvider
     *
     * @param ModifyArray $modifyArray
     * @param string $exceptionMessage
     * @param bool $expectException
     * @param mixed $expected
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     *
     * @throws ActionException
     */
    public function testFailRun(
        ModifyArray $modifyArray,
        string $exceptionMessage,
        bool $expectException,
        $expected,
        bool $isFatal = false,
        bool $isSuccessRequired = false
    ): void
    {
        $this->runBasicTest(
            $expectException,
            !$expectException,
            $modifyArray
                ->modifyContent([])
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired),
            $expected,
            $exceptionMessage
        );
    }

    /**
     * Test the action methods (i.e. addKey, modifyKey, removeKey).
     *
     * @dataProvider actionProvider
     *
     * @param ModifyArray $modifyAction
     * @param array $modifyContent
     * @param array $expected
     *
     * @throws ActionException
     */
    public function testActions(ModifyArray $modifyAction, array $modifyContent, array $expected): void
    {
        $result = $modifyAction->modifyContent($modifyContent)->run()->getResult();
        $this->assertEquals($expected, $result);
    }
}
