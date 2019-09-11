<?php

namespace Forte\Worker\Exceptions;

/**
 * Class WorkerException
 *
 * @package Forte\Worker\Exceptions
 */
class WorkerException extends \Exception
{
    /**
     * Return an array representation of this WorkerException instance.
     *
     * @return array Array representation of this WorkerException instance.
     */
    public function toArray(): array
    {
        $array = [];

        // The error message
        $array['error_message'] = $this->message;

        // The error code
        $array['error_code'] = $this->code;

        return $array;
    }
}
