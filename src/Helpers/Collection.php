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

namespace Forte\Worker\Helpers;

/**
 * Class Collection. A set of functions to handle collections.
 *
 * @package Forte\Worker\Helpers
 */
class Collection
{
    /**
     * Filter the given array and returns a sub-array containing only those
     * elements whose keys start with the given prefix.
     *
     * @param array $array An array to filter by key.
     * @param string $prefix The prefix to filter by row key.
     *
     * @return array An array whose keys starts with the given prefix.
     */
    public static function filterArrayByPrefixKey(array $array, string $prefix = ''): array
    {
        return array_filter(
            $array,
            function ($key) use ($prefix) {
                return (strpos($key, $prefix) === 0);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}