<?php

namespace Tests\Unit\Filters;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Filters\Arrays\ModifyArray;
use PHPUnit\Framework\TestCase;

/**
 * Class ModifyArrayTest
 *
 * @package Tests\Unit\Filters
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
            ['key1', ModifyArray::MODIFY_ADD, 'value1', "Add value 'value1' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, ['test-array' => 'array-value'], "Add value '{\"test-array\":\"array-value\"}' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, true, "Add value '1' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_ADD, null, "Add value '' with key 'key1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, 'value1', "Modify key 'key1' and set it to 'value1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, ['test-array' => 'array-value'], "Modify key 'key1' and set it to '{\"test-array\":\"array-value\"}'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, true, "Modify key 'key1' and set it to '1'"],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, null, "Modify key 'key1' and set it to ''"],
            ['key1', ModifyArray::MODIFY_REMOVE_KEY, 'value1', "Remove key 'key1'"],
            ['key1', "", 'value1', ""],
        ];
    }

    /**
     * Data provider to test changes on a 1-level array.
     *
     * @return array
     */
    public function changesProvider(): array
    {
        $simpleArray = ['test1' => 'value1', 'test2' => 'value2'];
        $simpleModifiedArray = ['test1' => 'value1', 'test2' => 'value2', 'key1' => 'value1'];
        return [
            ['key1', ModifyArray::MODIFY_ADD, 'value1', $simpleArray, $simpleModifiedArray],
            ['key1', ModifyArray::MODIFY_CHANGE_VALUE, 'value1', $simpleArray, $simpleModifiedArray],
            ['key1', ModifyArray::MODIFY_REMOVE_KEY, 'value1', $simpleArray, $simpleArray],
            ['test1', ModifyArray::MODIFY_REMOVE_KEY, 'value1', $simpleArray, ['test2' => 'value2']],
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
        $modifiedArrayThreeLevels = ['test1' => ['test3' => ['test4' => ['key1' => $addArray, 'test5' => 'value5']]], 'test2' => 'value2'];
        $modifiedArrayFourLevels = ['test1' => ['test3' => ['test4' => ['test6' => ['key1' => $addArray], 'test5' => 'value5']]], 'test2' => 'value2'];
        return [
            ['test1.test3.test4.key1', ModifyArray::MODIFY_ADD, $addArray, $array, $modifiedArrayThreeLevels],
            ['test1.test3.test4.test6.key1', ModifyArray::MODIFY_ADD, $addArray, $array, $modifiedArrayFourLevels],
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
    public function validationProvider(): array
    {
        return [
            ['key', ModifyArray::MODIFY_ADD, '', false, ['key' => '']],
            ['', ModifyArray::MODIFY_ADD, '', true, null],
            ['key1', 'wrong_action', '', true, null],
            ['', '', '', true, null],
        ];
    }

    /**
     * Tests the operation message.
     *
     * @dataProvider modificationsProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param string $expected
     */
    public function testOperationMessage(string $key, string $operation, $value, string $expected): void
    {
        $modifyArray = new ModifyArray($key, $operation, $value);
        $this->assertEquals($expected, $modifyArray->getOperationMessage());
    }

    /**
     * Tests the applyChangeByType() function.
     *
     * @dataProvider changesProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param array  $array
     * @param array  $expected
     */
    public function testApplyChangeByType(string $key, string $operation, $value, array $array, array $expected): void
    {
        $modifyArray = new ModifyArray($key, $operation, $value);
        $modifyArray->applyChangeByType($array, $key, $operation, $value);
        $this->assertEquals($expected, $array);
    }

    /**
     * Tests the applyChangeToArray() function.
     *
     * @dataProvider complexChangesProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param array  $array
     * @param array  $expected
     */
    public function testApplyChangeToArray(string $key, string $operation, $value, array $array, array $expected): void
    {
        $modifyArray = new ModifyArray($key, $operation, $value);
        $modifyArray->applyChangeToArray($array, $key, $operation, $value);
        $this->assertEquals($expected, $array);
    }

    /**
     * Tests the isValid() function.
     *
     * @dataProvider validationProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed $value
     * @param bool $expectGeneratorException
     *
     * @throws \Forte\Api\Generator\Exceptions\GeneratorException
     */
    public function testIsValid(string $key, string $operation, $value, bool $expectGeneratorException): void
    {
        $modifyArray = new ModifyArray($key, $operation, $value);

        if ($expectGeneratorException) {
            $this->expectException(GeneratorException::class);
            $isValid = $modifyArray->isValid();
            $this->assertFalse($isValid);
        } else {
            $isValid = $modifyArray->isValid();
            $this->assertTrue($isValid);
        }
    }

    /**
     * Tests the filter() function.
     *
     * @dataProvider complexChangesProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param array  $array
     * @param array  $expected
     *
     * @throws \Forte\Api\Generator\Exceptions\GeneratorException
     */
    public function testFilter(string $key, string $operation, $value, array $array, array $expected): void
    {
        $modifyArray = new ModifyArray($key, $operation, $value);
        $modifiedArray = $modifyArray->filter($array);
        $this->assertEquals($expected, $modifiedArray);
    }

    /**
     * Tests the filter() function failures.
     *
     * @dataProvider validationProvider
     *
     * @param string $key
     * @param string $operation
     * @param mixed  $value
     * @param bool   $expectGeneratorException
     * @param mixed  $expected
     *
     * @throws \Forte\Api\Generator\Exceptions\GeneratorException
     */
    public function testFailFilter(string $key, string $operation, $value, bool $expectGeneratorException, $expected): void
    {
        $modifyArray = new ModifyArray($key, $operation, $value);
        if ($expectGeneratorException) {
            $this->expectException(GeneratorException::class);
            $modifyArray->filter([]);
        } else {
            $modifiedArray = $modifyArray->filter([]);
            $this->assertEquals($expected, $modifiedArray);
        }
    }
}