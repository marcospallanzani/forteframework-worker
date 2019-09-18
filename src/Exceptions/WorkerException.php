<?php

namespace Forte\Worker\Exceptions;

use Forte\Stdlib\ArrayableInterface;
use Forte\Stdlib\Exceptions\GeneralException;

/**
 * Class WorkerException
 *
 * @package Forte\Worker\Exceptions
 */
class WorkerException extends GeneralException implements ArrayableInterface
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
