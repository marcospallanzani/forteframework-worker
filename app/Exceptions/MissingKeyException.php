<?php

namespace Forte\Api\Generator\Exceptions;

use Throwable;

/**
 * Class MissingKeyException.
 *
 * Exception used to throw an error when a required configuration key is missing.
 *
 * @package Forte\Api\Generator\Exceptions
 */
class MissingKeyException extends GeneratorException
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
}
