<?php

namespace Forte\Worker\Exceptions;

use Forte\Worker\Transformers\Transforms\AbstractTransform;
use Throwable;

/**
 * Class TransformException
 *
 * @package Forte\Worker\Exceptions
 */
class TransformException extends WorkerException
{
    /**
     * @var AbstractTransform
     */
    protected $transform;

    /**
     * TransformException constructor.
     *
     * @param AbstractTransform $transform The AbstractTransform subclass instance that generated the error
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous
     */
    public function __construct(
        AbstractTransform $transform,
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->transform = $transform;
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
