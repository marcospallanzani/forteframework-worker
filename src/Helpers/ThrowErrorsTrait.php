<?php

namespace Forte\Worker\Helpers;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
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
     * @param ActionResult $actionResult The ActionResult instance that wraps the
     * AbstractAction subclass instance, that originated an error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ActionException
     */
    public function throwActionException(
        ActionResult $actionResult,
        string $message,
        string ...$parameters
    ): void
    {
        throw $this->getActionException($actionResult, vsprintf($message, $parameters));
    }

    /**
     * Return an ActionException with the given message and parameters.
     *
     * @param ActionResult $actionResult The ActionResult instance that wraps the
     * AbstractAction subclass instance, that originated an error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return ActionException
     */
    public function getActionException(
        ActionResult $actionResult,
        string $message,
        string ...$parameters
    ): ActionException
    {
        return new ActionException($actionResult, vsprintf($message, $parameters));
    }

    /**
     * Throw an ActionException with given failed children actions, message
     * and parameters.
     *
     * @param ActionResult $actionResult The ActionResult instance that wraps the
     * AbstractAction subclass instance, that originated an error.
     * @param array $failedChildrenActionResults The list of failed nested action
     * result objects (i.e. pre-, post- or nested-run actions of the current
     * failed action).
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ActionException
     */
    public function throwActionExceptionWithChildren(
        ActionResult $actionResult,
        array $failedChildrenActionResults,
        string $message,
        string ...$parameters
    ): void
    {
        $actionException = new ActionException($actionResult, vsprintf($message, $parameters));
        foreach ($failedChildrenActionResults as $failedChildrenAction) {
            if ($failedChildrenAction instanceof AbstractAction) {
                $actionException->addFailedChildAction($failedChildrenAction);
            }
        }
        throw $actionException;
    }
}