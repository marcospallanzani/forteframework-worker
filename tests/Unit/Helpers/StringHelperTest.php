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

namespace Forte\Worker\Tests\Unit\Helpers;

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Helpers\StringHelper;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class StringHelperTest
 *
 * @package Forte\Worker\Tests\Unit\Helpers
 */
class StringHelperTest extends BaseTest
{
    /**
     * Data provider for stringify tests.
     *
     * @return array
     */
    public function stringifyProvider(): array
    {
        $stdClass = new \stdClass();
        $stdClass->test1 = "value1";

        return [
            [null, 'null'],
            [false, 'false'],
            [true, 'true'],
            ['regular string', 'regular string'],
            [100, '100'],
            ['100', '100'],
            [100.10, '100.1'],
            [['test1' => 'value1'], '{"test1":"value1"}'],
            [$stdClass, 'Class type: stdClass. Object value: {"test1":"value1"}.'],
            [ActionFactory::createFileExists(__FILE__), "Class type: Forte\Worker\Actions\Checks\Files\FileExists. Object value: Check if file '".__FILE__."' exists.."],
        ];
    }

    /**
     * Test the StringHelper::stringifyVariable() method.
     *
     * @dataProvider stringifyProvider
     *
     * @param mixed $variable
     * @param string $expected
     */
    public function testStringifyVariable($variable, string $expected): void
    {
        $this->assertEquals($expected, StringHelper::stringifyVariable($variable));
    }
}