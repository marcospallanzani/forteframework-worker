<?php

namespace Forte\Worker\Helpers;

use Forte\Worker\Exceptions\CheckException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Exceptions\TransformException;
use Forte\Worker\Transformers\Transforms\AbstractTransform;
use Forte\Worker\Checkers\Checks\AbstractCheck;

/**
 * Trait ThrowErrorsTrait. Methods to easily throw application exceptions.
 *
 * @package Forte\Worker\Helpers
 */
trait ThrowErrorsTrait
{
    /**
     * Throw a GeneratorException with the given message and parameters.
     *
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws WorkerException
     */
    public function throwWorkerException(string $message, string ...$parameters): void
    {
        throw new WorkerException(vsprintf($message, $parameters));
    }

    /**
     * Throw a CheckException with the given message and parameters.
     *
     * @param AbstractCheck $check
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws CheckException
     */
    public function throwCheckException(
        AbstractCheck $check,
        string $message,
        string ...$parameters
    ): void
    {
        throw new CheckException($check, vsprintf($message, $parameters));
    }

    /**
     * Throw a TransformException with the given message and parameters.
     *
     * @param AbstractTransform $transform
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws TransformException
     */
    public function throwTransformException(
        AbstractTransform $transform,
        string $message,
        string ...$parameters
    ): void
    {
        throw new TransformException($transform, vsprintf($message, $parameters));
    }
}