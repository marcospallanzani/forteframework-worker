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

use Forte\Worker\Exceptions\MissingKeyException;
use Forte\Worker\Helpers\Collection;
use Tests\Unit\BaseTest;

/**
 * Class CollectionTest.
 *
 * @package Tests\Unit\Helpers
 */
class CollectionTest extends BaseTest
{
    /**
     * A general test array.
     *
     * @var array
     */
    protected $testArray = [
        'test1' => [
            'test2' => 'value2'
        ],
        'test5' => [
            'test6' => [
                'test7' => [
                    'test8' => [
                        'test9' => [
                            'test10' => 'value10'
                        ]
                    ]
                ],
            ]
        ],
    ];

    /**
     * Data provider for filter-by-prefix-key tests.
     *
     * @return array
     */
    public function collectionsProvider(): array
    {
        $filterCollection =  [
            "FILTER_0" => "FILTER_0_VALUE",
            "FILTER_1" => "FILTER_1_VALUE",
            "FILTER_2" => "FILTER_2_VALUE",
        ];

        $filterByStringCollection =  [
            "FILTER_BY_STRING_0" => "FILTER_BY_STRING_0_VALUE",
            "FILTER_BY_STRING_1" => "FILTER_BY_STRING_1_VALUE",
            "FILTER_BY_STRING_2" => "FILTER_BY_STRING_2_VALUE",
        ];

        $fullCollection = array_merge($filterCollection, $filterByStringCollection);

        return [
            [$fullCollection, "FILTER", $fullCollection],
            [$fullCollection, "FILTER_0", ["FILTER_0" => "FILTER_0_VALUE"]],
            [$fullCollection, "FILTER_BY", $filterByStringCollection],
            [$fullCollection, "FILTER_BY_STRING", $filterByStringCollection],
            [$fullCollection, "FILTER_BY_STRING_0", ["FILTER_BY_STRING_0" => "FILTER_BY_STRING_0_VALUE"]],
        ];
    }


    /**
     * Data provider for all config access tests.
     *
     * @return array
     */
    public function configProvider(): array
    {
        return [
            //  access key | content to be checked | expected value for the given key | an exception is expected
            ['test1', $this->testArray, ['test2' => 'value2'], false],
            ['WRONG-KEY', $this->testArray, ['test2' => 'value2'], true],
            ['test1.test2', $this->testArray, 'value2', false],
            ['test1.WRONG-KEY', $this->testArray, 'value2', true],
            ['test5.test6.test7.test8.test9', $this->testArray, ['test10' => 'value10'], false],
            ['test5.test6.test7.WRONG-KEY.test9', $this->testArray, ['test10' => 'value10'], true],
        ];
    }

    /**
     * Test method Collection::filterArrayByPrefixKey().
     *
     * @dataProvider collectionsProvider
     *
     * @param array $initialArray
     * @param string $prefix
     * @param array $expected
     */
    public function testFilterArrayByPrefixKey(array $initialArray, string $prefix, array $expected): void
    {
        $this->assertEquals($expected, Collection::filterArrayByPrefixKey($initialArray, $prefix));
    }

    /**
     * Test method Collection::variablesToArray().
     */
    public function testToVariablesToArray(): void
    {
        $anonymousAction = $this->getAnonymousActionClass();
        $childAction = $this->getAnonymousActionClass();

        // We build the expected output
        $arrayAnonymousAction = $anonymousAction->toArray();
        $anonymousAction->addBeforeAction($childAction);
        $arrayAnonymousAction['beforeActions'][] = $childAction->toArray();

        $this->assertEquals(['key' => 'value1', 'value', true, $arrayAnonymousAction], Collection::variablesToArray(['key' => 'value1', 'value', true, $anonymousAction]));
    }


    /**
     * Test the method Collection::getRequiredNestedConfigValue().
     *
     * @dataProvider configProvider
     *
     * @param string $key
     * @param array $checkContent
     * @param mixed $expectedValue
     * @param bool $expectException
     *
     * @throws MissingKeyException
     */
    public function testRequiredNestedConfigValue(
        string $key,
        array $checkContent,
        $expectedValue,
        bool $expectException
    ): void
    {
        if ($expectException) {
            $this->expectException(MissingKeyException::class);
        }
        $this->assertEquals($expectedValue, Collection::getRequiredNestedArrayValue($key, $checkContent));
    }
}
