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
    public static function getClassConstants($prefix = '')
    {
        $reflectClass = new \ReflectionClass(static::class);
        $constants = $reflectClass->getConstants();
        if ($prefix !== '') {
            // Filter constants by the given prefix
            $constants = array_filter(
                $constants,
                function ($key) use ($prefix) {
                    return (strpos($key, $prefix) === 0);
                },
                ARRAY_FILTER_USE_KEY
            );

        }
        return $constants;
    }
}
