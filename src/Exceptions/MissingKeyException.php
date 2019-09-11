<?php

namespace Forte\Worker\Exceptions;

use Throwable;

/**
 * Class MissingKeyException.
 *
 * Exception used to throw an error when a required configuration key is missing.
 *
 * @package Forte\Worker\Exceptions
 */
class MissingKeyException extends WorkerException
{
    /**
     * The missing key.
     *
     * @var string
     */
    protected $missingKey;

    /**
     * MissingKeyException constructor.
     *
     * @param string $missingKey
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $missingKey, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->missingKey = $missingKey;
    }

    /**
     * Returns the missing config key.
     *
     * @return string
     */
    public function getMissingKey(): string
    {
        return $this->missingKey;
    }

    /**
     * Return an array representation of this WorkerException instance.
     *
     * @return array Array representation of this WorkerException instance.
     */
    public function toArray(): array
    {
        $array = [];

        // The missing key
        $array['missing_key'] = $this->missingKey;

        // The error message
        $array['error_message'] = $this->message;

        // The error code
        $array['error_code'] = $this->code;

        return $array;
    }
}
