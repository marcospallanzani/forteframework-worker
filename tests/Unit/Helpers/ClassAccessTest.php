<?php

namespace Tests\Unit\Helpers;

use Forte\Api\Generator\Helpers\ClassAccessTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ClassAccessTest
 *
 * @package Tests\Unit\Helpers
 */
class ClassAccessTest extends TestCase
{
    /**
     * Returns an anonymous class to test ClassAccessTrait.
     *
     * @return @298
     */
    protected function getAnonymousTestClass()
    {
        return new class {

            use ClassAccessTrait;

            const TEST_PREFIX_0 = 0;
            const TEST_PREFIX_1 = 1;
            const TEST_PREFIX_2 = 2;
            const TEST_PREFIX_3 = 3;
            const TEST_PREFIX_4 = 4;

            static $TEST_PREFIX_5 = 5;
            static $TEST_PREFIX_6 = 6;
            static $TEST_PREFIX_7 = 7;
            static $TEST_PREFIX_8 = 8;
            static $TEST_PREFIX_9 = 9;
        };
    }

    /**
     * Tests the ClassAccessTrait::getClassConstants() method.
     */
    public function testClassConstants(): void
    {
        $class = $this->getAnonymousTestClass();
        $constants = $class::getClassConstants('TEST_PREFIX');
        $this->assertIsArray($constants);
        $this->assertCount(5, $constants);
        $this->assertArrayHasKey('TEST_PREFIX_0', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_1', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_2', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_3', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_4', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_5', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_6', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_7', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_8', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_9', $constants);
    }

    /**
     * Tests the ClassAccessTrait::getClassStaticProperties() method.
     */
    public function testClassStaticProperties(): void
    {
        $class = $this->getAnonymousTestClass();
        $constants = $class::getClassStaticProperties('TEST_PREFIX');
        $this->assertIsArray($constants);
        $this->assertCount(5, $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_0', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_1', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_2', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_3', $constants);
        $this->assertArrayNotHasKey('TEST_PREFIX_4', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_5', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_6', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_7', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_8', $constants);
        $this->assertArrayHasKey('TEST_PREFIX_9', $constants);
    }
}