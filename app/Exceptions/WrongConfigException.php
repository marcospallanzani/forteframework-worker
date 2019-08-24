<?php

namespace Forte\Api\Generator\Exceptions;

use Throwable;

/**
 * Class WrongConfigException
 *
 * @package Forte\Api\Generator\Exceptions
 */
class WrongConfigException extends \Exception
{
    /**
     * The wrong-configured key.
     *
     * @var string
     */
    protected $wrongKey;

    /**
     * WrongConfigException constructor.
     *
     * @param string $wrongKey
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $wrongKey, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->wrongKey = $wrongKey;
    }

    /**
     * Returns the wrong-configured config key.
     *
     * @return string
     */
    public function getWrongKey(): string
    {
        return $this->wrongKey;
    }
}