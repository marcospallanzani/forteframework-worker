<?php

namespace Forte\Worker\Helpers;

/**
 * Class Dates.
 *
 * @package Forte\Worker\Helpers
 */
class Dates
{
    /**
     * Format constants
     */
    const DATE_FORMAT_FULL_MICRO_TIME = 'Y-m-d H:i:s.u e';

    /**
     * Convert the given micro time to a date string, by using the given date format.
     *
     * @param float $microTime The micro time to be converted.
     * @param string $format The date format to be used.
     *
     * @return string A formatted data string representing the given micro time.
     */
    public static function formatMicroTime(
        float $microTime,
        string $format = self::DATE_FORMAT_FULL_MICRO_TIME
    ): string
    {
        return date_create_from_format( 'U.u', number_format($microTime, 6, '.', ''))->format($format);
    }
}
