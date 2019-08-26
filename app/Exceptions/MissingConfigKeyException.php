<?php

namespace Forte\Api\Generator\Exceptions;

use Throwable;

/**
 * Class MissingConfigKeyException.
 *
 * Exception used to throw an error when a required configuration key is missing.
 *
 * @package Forte\Api\Generator\Exceptions
 */
class MissingConfigKeyException extends GeneratorException
{
    /**
     * The missing key.
     *
     * @var string
     */
    protected $missingKey;

    /**
     * MissingConfigKeyException constructor.
     *
     * @param string $wrongKey
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $wrongKey, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->missingKey = $wrongKey;
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
