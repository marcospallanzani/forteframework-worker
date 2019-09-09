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

use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Helpers\StringParser;
use Tests\Unit\BaseTest;

/**
 * Class StringParserTest
 *
 * @package Tests\Unit\Helpers
 */
class StringParserTest extends BaseTest
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
            [new EmptyTransform(), 'Class type: Forte\Worker\Actions\Transforms\EmptyTransform. Object value: Empty transform.'],
        ];
    }

    /**
     * Test the StringParser::startsWith() method.
     */
    public function testStartsWith(): void
    {
        $this->assertTrue(StringParser::startsWith("This is a test", "This is"));
        $this->assertFalse(StringParser::startsWith("This is a test", "Another string"));
        $this->assertFalse(StringParser::startsWith("This is a test", "Another longer string"));
    }

    /**
     * Test the StringParser::endsWith() method.
     */
    public function testEndsWith(): void
    {
        $this->assertTrue(StringParser::endsWith("This is a test", ""));
        $this->assertTrue(StringParser::endsWith("This is a test", "test"));
        $this->assertFalse(StringParser::endsWith("This is a test", "Another string"));
        $this->assertFalse(StringParser::endsWith("This is a test", "Another longer string"));
    }

    /**
     * Test the StringParser::stringifyVariable() method.
     *
     * @dataProvider stringifyProvider
     *
     * @param mixed $variable
     * @param string $expected
     */
    public function testStringifyVariable($variable, string $expected): void
    {
        $this->assertEquals($expected, StringParser::stringifyVariable($variable));
    }

    /**
     * Test the StringParser::getFormattedMessage() method.
     */
    public function testFormatMessage(): void
    {
        $this->assertEquals('test formatted string', StringParser::getFormattedMessage('test %s %s', 'formatted', 'string'));
        $this->assertEquals('test formatted 10', StringParser::getFormattedMessage('test %s %d', 'formatted', 10.01));
        $this->assertStringStartsWith('test formatted 10.01', StringParser::getFormattedMessage('test %s %f', 'formatted', 10.01));
    }
}