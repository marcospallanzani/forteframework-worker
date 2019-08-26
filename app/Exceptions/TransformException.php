<?php

namespace Forte\Api\Generator\Exceptions;

use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;
use Throwable;

/**
 * Class TransformException
 *
 * @package Forte\Api\Generator\Exceptions
 */
class TransformException extends GeneratorException
{
    /**
     * @var AbstractTransform
     */
    protected $transform;

    /**
     * TransformException constructor.
     *
     * @param AbstractTransform $transformer The AbstractTransform subclass instance that generated the error
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous
     */
    public function __construct(
        AbstractTransform $transformer,
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->transform = $transformer;
    }

    /**
     * Returns the AbstractTransform subclass instance that generated the error.
     *
     * @return AbstractTransform the AbstractTransform subclass instance that generated the error
     */
    public function getTransform(): AbstractTransform
    {
        return $this->transform;
    }
}
