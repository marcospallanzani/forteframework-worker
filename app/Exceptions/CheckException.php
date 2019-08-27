<?php

namespace Forte\Api\Generator\Exceptions;

use Forte\Api\Generator\Transformers\Transforms\Checks\AbstractCheck;
use Throwable;

/**
 * Class CheckException
 *
 * @package Forte\Api\Generator\Exceptions
 */
class CheckException extends GeneratorException
{
    /**
     * @var AbstractCheck
     */
    protected $check;

    /**
     * CheckException constructor.
     *
     * @param AbstractCheck $check The AbstractCheck subclass instance that generated the error
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous
     */
    public function __construct(
        AbstractCheck $check,
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->check = $check;
    }

    /**
     * Returns the AbstractCheck subclass instance that generated the error.
     *
     * @return AbstractCheck the AbstractCheck subclass instance that generated the error
     */
    public function getCheck(): AbstractCheck
    {
        return $this->check;
    }
}