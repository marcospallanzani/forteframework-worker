<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Arrays;

use Forte\Worker\Actions\ActionInterface;
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
        // key | action | value | expected
        return [
            ['key1', ModifyArray::MODIFY_ADD_KEY, 'value1', "Add value 'value1' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD_KEY, ['test-array' => 'array-value'], "Add value '{\"test-array\":\"array-value\"}' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD_KEY, true, "Add value 'true' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD_KEY, null, "Add value 'null' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, 'value1', "Modify value with key 'key1' and set it to 'value1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, ['test-array' => 'array-value'], "Modify value with key 'key1' and set it to '{\"test-array\":\"array-value\"}'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, true, "Modify value with key 'key1' and set it to 'true'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, null, "Modify value with key 'key1' and set it to 'null'"],
            ['key1', ModifyArray::MODIFY_CHANGE_KEY, 'key2', "Change key 'key1' and set it to 'key2'"],
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
        $arrayFourLevels = ['test1' => ['test3' => ['test4' => ['test5' => ['test6' => 'value6']]]], 'test2' => 'value2'];
        $modifiedOneLevel = ['test1' => ['test3' => ['test4' => ['test5' => 'value5']]], 'test2' => 'value2', 'test6' => 'value6'];
        $modifiedArrayThreeLevels = ['test1' => ['test3' => ['test4' => ['key1' => $addArray, 'test5' => 'value5']]], 'test2' => 'value2'];
        $modifiedArrayThreeLevelsChangeKey = ['test1' => ['test3' => ['test4' => ['test6' => 'value5']]], 'test2' => 'value2'];
        $modifiedArrayFourLevels = ['test1' => ['test3' => ['test4' => ['test6' => ['key1' => $addArray], 'test5' => 'value5']]], 'test2' => 'value2'];
        $modifiedArrayFourLevelsChangeKey = ['test1' => ['test3' => ['test4' => ['test5' => ['test7' => 'value6']]]], 'test2' => 'value2'];
        return [
            // string key | string action | value | array array | array expected
            ['test6', ModifyArray::MODIFY_ADD_KEY, 'value6', $array, $modifiedOneLevel],
            ['test1.test3.test4.key1', ModifyArray::MODIFY_ADD_KEY, $addArray, $array, $modifiedArrayThreeLevels],
            ['test1.test3.test4.test6.key1', ModifyArray::MODIFY_ADD_KEY, $addArray, $array, $modifiedArrayFourLevels],
            ['added', ModifyArray::MODIFY_CHANGE_KEY, 'test66', $addArray, ['test66' => 'value']],
            ['test1.test3.test4.test5', ModifyArray::MODIFY_CHANGE_KEY, 'test6', $array, $modifiedArrayThreeLevelsChangeKey],
            ['test1.test3.test4.test5.test6', ModifyArray::MODIFY_CHANGE_KEY, 'test7', $arrayFourLevels, $modifiedArrayFourLevelsChangeKey],
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
        // Action | exception message | expect exception
        return [
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_ADD_KEY, 'value'),
                '',
                false,
            ],
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_CHANGE_KEY, 'value'),
                '',
                false,
            ],
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_CHANGE_VALUE, 'value'),
                '',
                false,
            ],
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_REMOVE_KEY),
                '',
                false,
            ],
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_CHANGE_KEY),
                'Action modify_change_key requires a value. None or empty value was given.',
                true,
            ],
            [
                ActionFactory::createModifyArray('', ModifyArray::MODIFY_ADD_KEY),
                "No key specified",
                true,
            ],
            [
                $modifyWrongAction = ActionFactory::createModifyArray('key1', 'wrong_action'),
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
            ],
            [
                ActionFactory::createModifyArray('', ''),
                "No key specified",
                true,
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
        // Action | is valid | exception message | expect exception | expected value | severity | content
//TODO MISSING SUCCESS REQUIRED CASE
        return [
            [
                ActionFactory::createModifyArray('key', ModifyArray::MODIFY_ADD_KEY),
                true,
                '',
                false,
                ['key' => '']
            ],
            [
                ActionFactory::createModifyArray('', ModifyArray::MODIFY_ADD_KEY),
                false,
                "No key specified",
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_FATAL,
            ],
            [
                ActionFactory::createModifyArray('', ModifyArray::MODIFY_ADD_KEY),
                false,
                "No key specified",
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL
            ],
            [
                ActionFactory::createModifyArray('', ''),
                false,
                "No key specified",
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_FATAL
            ],
            [
                ActionFactory::createModifyArray('', ''),
                false,
                "No key specified",
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL
            ],
            [
                $modifyWrongAction = ActionFactory::createModifyArray('key1', 'wrong_action'),
                false,
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_FATAL
            ],
            [
                $modifyWrongAction = ActionFactory::createModifyArray('key1', 'wrong_action'),
                false,
                sprintf(
                    "Action wrong_action not supported. Supported actions are [%s]",
                    implode(', ', $modifyWrongAction->getSupportedActions())
                ),
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL
            ],
            [
                $modifyKeyAlreadyExists = ActionFactory::createModifyArray()->changeKey('key1', 'key2'),
                true,
                sprintf(
                    "It is not possible to override an existing key, when using action [%s]",
                    ModifyArray::MODIFY_CHANGE_KEY
                ),
                true,
                null,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL,
                ['key1' => 'value1', 'key2' => 'value2']
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
        $testArray = ['key1' => 'value1', 'key2' => '', 'key5' => ['key6' => 'value6']];

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
                ActionFactory::createModifyArray()->changeValueByKey('key3', 'value3'),
                $testArray,
                array_merge($testArray, ['key3' => 'value3'])
            ],
            [
                ActionFactory::createModifyArray()->changeValueByKey('key2', 'value2'),
                $testArray,
                array_merge($testArray, ['key2' => 'value2'])
            ],
            [
                ActionFactory::createModifyArray()->changeKey('key1', 'key3'),
                $testArray,
                ['key3' => 'value1', 'key2' => '', 'key5' => ['key6' => 'value6']]
            ],
            [
                ActionFactory::createModifyArray()->changeKey('key5.key6', 'key7'),
                $testArray,
                ['key1' => 'value1', 'key2' => '', 'key5' => ['key7' => 'value6']]
            ],
            [
                ActionFactory::createModifyArray()->removeKey('key2'),
                $testArray,
                ['key1' => 'value1', 'key5' => ['key6' => 'value6']]
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
     * @param bool $isValid
     * @param string $exceptionMessage
     * @param bool $expectException
     * @param mixed $expected
     * @param int $actionSeverity
     * @param array $content
     *
     * @throws ActionException
     */
    public function testFailRun(
        ModifyArray $modifyArray,
        bool $isValid,
        string $exceptionMessage,
        bool $expectException,
        $expected,
        int $actionSeverity = ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
        array $content = []
    ): void
    {
        $this->runBasicTest(
            $expectException,
            $isValid,
            $modifyArray
                ->modifyContent($content)
                ->setActionSeverity($actionSeverity),
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
