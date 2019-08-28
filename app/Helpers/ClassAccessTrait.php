<?php

namespace Forte\Api\Generator\Helpers;

/**
 * Trait ClassAccessTrait. A trait that identifies class constants.
 *
 * @package Forte\Api\Generator\Helpers
 */
trait ClassAccessTrait
{
    /**
     * Get all class constants by prefixed name.
     *
     * @param string $prefix The prefix to filter class constants by.
     * An empty string will return all class constants.
     *
     * @return array An array whose keys are class constant names,
     * and whose values are their values.
     *
     * @throws \ReflectionException
     */
    public static function getClassConstants(string $prefix = ''): array
    {
        $reflectClass = new \ReflectionClass(static::class);
        $constants = $reflectClass->getConstants();
        if ($prefix !== '') {
            // Filter constants by the given prefix
            $constants = self::filterArrayByKey($constants, $prefix);
        }
        return $constants;
    }

    /**
     * Get all class static properties by prefixed name.
     *
     * @param string $prefix The prefix to filter class static property by.
     * An empty string will return all class static properties.
     *
     * @return array An array whose keys are class static property names,
     * and whose values are their values.
     *
     * @throws \ReflectionException
     */
    public static function getClassStaticProperties(string $prefix = ''): array
    {
        $reflectClass = new \ReflectionClass(static::class);
        $constants = $reflectClass->getStaticProperties();
        if ($prefix !== '') {
            // Filter constants by the given prefix
            $constants = self::filterArrayByKey($constants, $prefix);
        }
        return $constants;
    }

    /**
     * Filter the given array and returns a sub-array containing only those
     * elements whose keys start with the given prefix.
     *
     * @param array $array An array to filter by key.
     * @param string $prefix The prefix to filter by row key.
     *
     * @return array An array whose keys starts with the given prefix.
     */
    public static function filterArrayByKey(array $array, string $prefix = ''): array
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
