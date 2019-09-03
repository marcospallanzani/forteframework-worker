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
     */
    public static function getClassConstants(string $prefix = ''): array
    {
        $constants = [];
        try {
            $reflectClass = new \ReflectionClass(static::class);
            $constants = $reflectClass->getConstants();
            if ($prefix !== '') {
                // Filter constants by the given prefix
                $constants = Collection::filterArrayByPrefixKey($constants, $prefix);
            }
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $reflectionException) {
            // In this case, as we use the static key work, a ReflectionException is never thrown.
            ;
            // @codeCoverageIgnoreEnd
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
     */
    public static function getClassStaticProperties(string $prefix = ''): array
    {
        $properties = [];
        try {
            $reflectClass = new \ReflectionClass(static::class);
            $properties = $reflectClass->getStaticProperties();
            if ($prefix !== '') {
                // Filter constants by the given prefix
                $properties = Collection::filterArrayByPrefixKey($properties, $prefix);
            }
            // @codeCoverageIgnoreStart
        } catch (\ReflectionException $reflectionException) {
            // In this case, as we use the static key work, a ReflectionException is never thrown.
            ;
            // @codeCoverageIgnoreEnd
        }
        return $properties;
    }
}
