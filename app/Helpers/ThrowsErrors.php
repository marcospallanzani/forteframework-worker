<?php

namespace Forte\Api\Generator\Helpers;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;
use Forte\Api\Generator\Transformers\Transforms\Checks\AbstractCheck;

/**
 * Trait ThrowsErrors. Methods to easily throw application exceptions.
 *
 * @package Forte\Api\Generator\Helpers
 */
trait ThrowsErrors
{
    /**
     * Throws a GeneratorException with the given message and parameters.
     *
     * @param string $message The exception message
     * @param string ...$parameters The values to replace in the error message
     *
     * @throws GeneratorException
     */
    public function throwGeneratorException(string $message, string ...$parameters): void
    {
        throw new GeneratorException(vsprintf($message, $parameters));
    }

    /**
     * Throws a CheckException with the given message and parameters.
     *
     * @param AbstractCheck $check
     * @param string $message The exception message
     * @param string ...$parameters The values to replace in the error message
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
     * Throws a TransformException with the given message and parameters.
     *
     * @param AbstractTransform $transform
     * @param string $message The exception message
     * @param string ...$parameters The values to replace in the error message
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