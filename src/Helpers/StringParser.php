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
 * Class StringParser. A set of methods to parse and handle strings.
 *
 * @package Forte\Worker\Helpers
 */
class StringParser
{
    /**
     * Check if the given check string starts with the given search string.
     *
     * @param string $check The string to be checked.
     * @param string $startsWith The expected starts-with string.
     *
     * @return bool True if the given check string starts with the given
     * search string; false otherwise.
     */
    public static function startsWith(string $check, string $startsWith): bool
    {
        $length = strlen($startsWith);
        return (substr($check, 0, $length) === $startsWith);
    }

    /**
     * Check if the given check string ends with the given search string.
     *
     * @param string $check The string to be checked.
     * @param string $endsWith The expected ends-with string.
     *
     * @return bool True if the given check string ends with the given
     * search string; false otherwise.
     */
    public static function endsWith(string $check, string $endsWith): bool
    {
        $length = strlen($endsWith);
        if ($length == 0) {
            return true;
        }
        return (substr($check, -$length) === $endsWith);
    }

    /**
     * Returns a string version of the given value.
     * (arrays are converted to a json string).
     *
     * @param mixed $value The value to be converted to a string.
     *
     * @return string
     */
    public static function stringifyResult($value): string
    {
        if (is_array($value)) {
            return json_encode($value);
        } elseif (is_object($value)) {
            return sprintf(
                "Class type: %s. Object value: %s.",
                get_class($value),
                (string) $value
            );
        } elseif (is_bool($value)) {
            return (boolval($value) ? 'true' : 'false');
        } elseif (is_null($value)) {
            return "NULL";
        } else {
            return (string) $value;
        }
    }
}