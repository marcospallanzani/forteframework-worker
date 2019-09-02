<?php

namespace Tests\Unit\Helpers;

use Forte\Worker\Helpers\ClassAccessTrait;
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
     * @return object
     */
    protected function getAnonymousTestClass()
    {
        return new class {

            use ClassAccessTrait;

            const TEST_PREFIX_0_0 = 0;
            const TEST_PREFIX_0_1 = 1;
            const TEST_PREFIX_0_2 = 2;
            const TEST_PREFIX_0_3 = 3;
            const TEST_PREFIX_0_4 = 4;
            const TEST_PREFIX_1_0 = 5;
            const TEST_PREFIX_1_1 = 6;
            const TEST_PREFIX_1_2 = 7;
            const TEST_PREFIX_1_3 = 8;
            const TEST_PREFIX_1_4 = 9;

            static $TEST_PREFIX_A_0 = 0;
            static $TEST_PREFIX_A_1 = 1;
            static $TEST_PREFIX_A_2 = 2;
            static $TEST_PREFIX_A_3 = 3;
            static $TEST_PREFIX_A_4 = 4;
            static $TEST_PREFIX_B_0 = 5;
            static $TEST_PREFIX_B_1 = 6;
            static $TEST_PREFIX_B_2 = 7;
            static $TEST_PREFIX_B_3 = 8;
            static $TEST_PREFIX_B_4 = 9;
        };
    }

    /**
     * Data provider for constants tests.
     *
     * @return array
     */
    public function constantsProvider(): array
    {
        return [
            [
                'TEST_PREFIX_0',
                5,
                ['TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4'],
                ['TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'],
            ],
            [
                'TEST_PREFIX_1',
                5,
                ['TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'],
                ['TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4'],
            ],
            [
                'TEST_PREFIX_',
                10,
                [
                    'TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4',
                    'TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'
                ],
                [],
            ],
            [
                'TEST_PREFIX_NOT_DEFINED',
                0,
                [],
                [
                    'TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4',
                    'TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'
                ],
            ],
            [
                '',
                10,
                [
                    'TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4',
                    'TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'
                ],
                [],
            ],
            [
                '',
                10,
                [
                    'TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4',
                    'TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'
                ],
                [
                    'TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4',
                    'TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'
                ],
            ],
        ];
    }

    /**
     * Data provider for properties tests.
     *
     * @return array
     */
    public function propertiesProvider(): array
    {
        return [
            [
                'TEST_PREFIX_A',
                5,
                ['TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4'],
                ['TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'],
            ],
            [
                'TEST_PREFIX_B',
                5,
                ['TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'],
                ['TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4'],
            ],
            [
                'TEST_PREFIX_',
                10,
                [
                    'TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4',
                    'TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'
                ],
                [],
            ],
            [
                'TEST_PREFIX_NOT_DEFINED',
                0,
                [],
                [
                    'TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4',
                    'TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'
                ],
            ],
            [
                '',
                10,
                [
                    'TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4',
                    'TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'
                ],
                [],
            ],
            [
                '',
                10,
                [
                    'TEST_PREFIX_A_0', 'TEST_PREFIX_A_1', 'TEST_PREFIX_A_2', 'TEST_PREFIX_A_3', 'TEST_PREFIX_A_4',
                    'TEST_PREFIX_B_0', 'TEST_PREFIX_B_1', 'TEST_PREFIX_B_2', 'TEST_PREFIX_B_3', 'TEST_PREFIX_B_4'
                ],
                [
                    'TEST_PREFIX_0_0', 'TEST_PREFIX_0_1', 'TEST_PREFIX_0_2', 'TEST_PREFIX_0_3', 'TEST_PREFIX_0_4',
                    'TEST_PREFIX_1_0', 'TEST_PREFIX_1_1', 'TEST_PREFIX_1_2', 'TEST_PREFIX_1_3', 'TEST_PREFIX_1_4'
                ],
            ],
        ];
    }

    /**
     * Tests the ClassAccessTrait::getClassConstants() method.
     *
     * @dataProvider constantsProvider
     *
     * @param string $prefix
     * @param int $expectedCount
     * @param array $expectedConstantsKeys
     * @param array $nonExpectedConstantsKeys
     */
    public function testClassConstants(
        string $prefix,
        int $expectedCount,
        array $expectedConstantsKeys,
        array $nonExpectedConstantsKeys
    ): void
    {
        $class = $this->getAnonymousTestClass();
        $constants = $class::getClassConstants($prefix);
        $this->assertEntries($constants, $expectedCount, $expectedConstantsKeys, $nonExpectedConstantsKeys);
    }

    /**
     * Tests the ClassAccessTrait::getClassStaticProperties() method.
     *
     * @dataProvider propertiesProvider
     *
     * @param string $prefix
     * @param int $expectedCount
     * @param array $expectedPropertiesKeys
     * @param array $nonExpectedPropertiesKeys
     */
    public function testClassStaticProperties(
        string $prefix,
        int $expectedCount,
        array $expectedPropertiesKeys,
        array $nonExpectedPropertiesKeys
    ): void
    {
        $class = $this->getAnonymousTestClass();
        $constants = $class::getClassStaticProperties($prefix);
        $this->assertEntries($constants, $expectedCount, $expectedPropertiesKeys, $nonExpectedPropertiesKeys);
    }

    /**
     * Checks if the given entries respect the given conditions (count, expected keys).
     *
     * @param array $entries
     * @param int $expectedCount
     * @param array $expectedKeys
     * @param array $nonExpectedKeys
     */
    protected function assertEntries(
        array $entries,
        int $expectedCount,
        array $expectedKeys,
        array $nonExpectedKeys
    ): void
    {
        $this->assertIsArray($entries);
        $this->assertCount($expectedCount, $entries);
        foreach ($expectedKeys as $expectedKey) {
            $this->assertArrayHasKey($expectedKey, $entries);
        }
        foreach ($nonExpectedKeys as $nonExpectedKey) {
            $this->assertArrayNotHasKey($nonExpectedKey, $entries);
        }
    }
}