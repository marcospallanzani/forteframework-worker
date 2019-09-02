<?php

namespace Forte\Worker\Helpers;

/**
 * Trait ClassAccessTrait. A trait that identifies class constants.
 *
 * @package Forte\Worker\Helpers
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
            $constants = Collection::filterArrayByPrefixKey($constants, $prefix);
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
            $constants = Collection::filterArrayByPrefixKey($constants, $prefix);
        }
        return $constants;
    }
}
