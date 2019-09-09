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

use Forte\Worker\Actions\ArrayableInterface;

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

    /**
     * Convert all elements in the given variables list to an array.
     *
     * @param array $variables
     *
     * @return array
     */
    public static function variablesToArray(array $variables): array
    {
        $toArray = [];
        foreach ($variables as $key => $variable) {
            if ($variable instanceof ArrayableInterface) {
                $toArray[$key] = $variable->toArray();
            } elseif (is_array($variable)) {
                $toArray[$key] = self::variablesToArray($variable);
            } else {
                $toArray[$key] = $variable;
            }
        }

        return $toArray;
    }
}