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

use Forte\Api\Generator\Helpers\Collection;
use PHPUnit\Framework\TestCase;

/**
 * Class CollectionTest.
 *
 * @package Tests\Unit\Helpers
 */
class CollectionTest extends TestCase
{
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
}
