<?php

namespace Forte\Worker\Exceptions;

use Forte\Worker\Transformers\AbstractTransformer;
use Throwable;

/**
 * Class TransformerException
 *
 * @package Forte\Worker\Exceptions
 */
class TransformerException extends GeneratorException
{
    /**
     * @var AbstractTransformer
     */
    protected $transformer;

    /**
     * TransformerException constructor.
     *
     * @param AbstractTransformer $transformer The AbstractTransformer subclass instance that generated the error
     * @param string $message The exception message
     * @param int $code The exception code
     * @param Throwable|null $previous
     */
    public function __construct(
        AbstractTransformer $transformer,
        string $message = "",
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->transformer = $transformer;
    }

    /**
     * Returns the AbstractTransformer subclass instance that generated the error.
     *
     * @return AbstractTransformer the AbstractTransformer subclass instance that generated the error
     */
    public function getTransformer(): AbstractTransformer
    {
        return $this->transformer;
    }
}
