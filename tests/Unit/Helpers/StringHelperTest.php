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

namespace Tests\Unit\Helpers;

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Helpers\StringHelper;
use Tests\Unit\BaseTest;

/**
 * Class StringHelperTest
 *
 * @package Tests\Unit\Helpers
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
            [ActionFactory::createEmptyTransform(), 'Class type: Forte\Worker\Actions\Transforms\EmptyTransform. Object value: Empty transform.'],
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