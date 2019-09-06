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
        throw $this->getActionException($action, vsprintf($message, $parameters));
    }

    /**
     * Return an ActionException with the given message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return ActionException
     */
    public function getActionException(
        AbstractAction $action,
        string $message,
        string ...$parameters
    ): ActionException
    {
        return new ActionException($action, vsprintf($message, $parameters));
    }

    /**
     * Throw an ActionException with given failed children actions, message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param array $childFailures The list of child failures (instances of ActionException),
     * i.e. errors generated by child processes of the AbstractAction subclass instance,
     * wrapped by this ActionException instance.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ActionException
     */
    public function throwActionExceptionWithChildren(
        AbstractAction $action,
        array $childFailures,
        string $message,
        string ...$parameters
    ): void
    {
        throw $this->getActionExceptionWithChildren($action, $childFailures, vsprintf($message, $parameters));
    }

    /**
     * Get an ActionException with given failed children actions, message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param array $childFailures The list of child failures (instances of ActionException),
     * i.e. errors generated by child processes of the AbstractAction subclass instance,
     * wrapped by this ActionException instance.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return ActionException
     */
    public function getActionExceptionWithChildren(
        AbstractAction $action,
        array $childFailures,
        string $message,
        string ...$parameters
    ): ActionException
    {
        $actionException = $this->getActionException($action, vsprintf($message, $parameters));
        foreach ($childFailures as $childFailure) {
            if ($childFailure instanceof ActionException) {
                $actionException->addChildFailure($childFailure);
            }
        }
        return $actionException;
    }
}