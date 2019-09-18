<?php

namespace Forte\Worker\Helpers;

use Forte\Stdlib\StringUtils;
use Forte\Worker\Actions\AbstractAction;

/**
 * Class StringHelper.
 *
 * @package Forte\Worker\Helpers
 */
class StringHelper extends StringUtils
{
    /**
     * Return a string version of the given variable (arrays are converted to a json string).
     *
     * @param mixed $variable The variable to be converted to a string.
     *
     * @return string String representation of the given variable
     */
    public static function stringifyVariable($variable): string
    {
        if ($variable instanceof AbstractAction) {
            return sprintf(
                "Class type: %s. Object value: %s.",
                get_class($variable),
                $variable
            );
        } else {
            return parent::stringifyVariable($variable);
        }
    }
}