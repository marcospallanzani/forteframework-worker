<?php

namespace Forte\Worker\Helpers;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;

/**
 * Trait ThrowErrorsTrait. Methods to easily throw application exceptions.
 *
 * @package Forte\Worker\Helpers
 */
trait ThrowErrorsTrait
{
    /**
     * Throw a WorkerException with the given message and parameters.
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
     * Throw an ActionException with the given message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ActionException
     */
    public function throwActionException(
        AbstractAction $action,
        string $message,
        string ...$parameters
    ): void
    {
        throw new ActionException($action, vsprintf($message, $parameters));
    }
}