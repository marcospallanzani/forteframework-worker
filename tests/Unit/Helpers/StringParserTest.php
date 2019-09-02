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

use Forte\Worker\Helpers\StringParser;
use PHPUnit\Framework\TestCase;

/**
 * Class StringParserTest
 *
 * @package Tests\Unit\Helpers
 */
class StringParserTest extends TestCase
{
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
        $this->assertTrue(StringParser::endsWith("This is a test", "test"));
        $this->assertFalse(StringParser::endsWith("This is a test", "Another string"));
        $this->assertFalse(StringParser::endsWith("This is a test", "Another longer string"));
    }
}