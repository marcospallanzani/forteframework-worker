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

use Forte\Worker\Actions\AbstractAction;

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
     * Return a string version of the given variable (arrays are converted to a json string).
     *
     * @param mixed $variable The variable to be converted to a string.
     *
     * @return string String representation of the given variable
     */
    public static function stringifyVariable($variable): string
    {
        if (is_array($variable)) {
            return json_encode($variable);
        } elseif ($variable instanceof AbstractAction) {
            return sprintf(
                "Class type: %s. Object value: %s.",
                get_class($variable),
                $variable
            );
        } elseif (is_object($variable)) {
            return sprintf(
                "Class type: %s. Object value: %s.",
                get_class($variable),
                self::stringifyVariable(get_object_vars($variable))
            );
        } elseif (is_bool($variable)) {
            return (boolval($variable) ? 'true' : 'false');
        } elseif (is_null($variable)) {
            return "null";
        } else {
            return (string) $variable;
        }
    }
}