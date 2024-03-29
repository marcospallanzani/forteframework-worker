<?php

namespace Forte\Worker\Exceptions;

use Forte\Worker\Actions\AbstractAction;

/**
 * Trait ThrowErrorsTrait. Methods to easily throw application exceptions.
 *
 * @package Forte\Worker\Exceptions
 */
trait ThrowErrorsTrait
{
    use \Forte\Stdlib\Exceptions\ThrowErrorsTrait;

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
        throw $this->getWorkerException($message, ...$parameters);
    }

    /**
     * Return a WorkerException with the given message and parameters.
     *
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return WorkerException
     */
    public function getWorkerException(string $message, string ...$parameters): WorkerException
    {
        return new WorkerException(vsprintf($message, $parameters));
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
        throw $this->getActionException($action, $message, ...$parameters);
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
     * Throw an ConfigurationException with the given message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ConfigurationException
     */
    public function throwConfigurationException(
        AbstractAction $action,
        string $message,
        string ...$parameters
    ): void
    {
        throw $this->getConfigurationException($action, $message, ...$parameters);
    }

    /**
     * Return an ConfigurationException with the given message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return ConfigurationException
     */
    public function getConfigurationException(
        AbstractAction $action,
        string $message,
        string ...$parameters
    ): ConfigurationException
    {
        return new ConfigurationException($action, vsprintf($message, $parameters));
    }

    /**
     * Throw an ValidationException with the given message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ValidationException
     */
    public function throwValidationException(
        AbstractAction $action,
        string $message,
        string ...$parameters
    ): void
    {
        throw $this->getValidationException($action, $message, ...$parameters);
    }

    /**
     * Return an ValidationException with the given message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return ValidationException
     */
    public function getValidationException(
        AbstractAction $action,
        string $message,
        string ...$parameters
    ): ValidationException
    {
        return new ValidationException($action, vsprintf($message, $parameters));
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
        throw $this->getActionExceptionWithChildren($action, $childFailures, $message, ...$parameters);
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
        $actionException = $this->getActionException($action, $message, ...$parameters);
        foreach ($childFailures as $childFailure) {
            if ($childFailure instanceof WorkerException) {
                $actionException->addChildFailure($childFailure);
            }
        }
        return $actionException;
    }

    /**
     * Throw an ValidationException with given failed children actions, message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param array $childFailures The list of child failures (instances of ActionException),
     * i.e. errors generated by child processes of the AbstractAction subclass instance,
     * wrapped by this ActionException instance.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @throws ValidationException
     */
    public function throwValidationExceptionWithChildren(
        AbstractAction $action,
        array $childFailures,
        string $message,
        string ...$parameters
    ): void
    {
        throw $this->getValidationExceptionWithChildren($action, $childFailures, $message, ...$parameters);
    }

    /**
     * Get an ValidationException with given failed children actions, message and parameters.
     *
     * @param AbstractAction $action The action that originated this error.
     * @param array $childFailures The list of child failures (instances of ActionException),
     * i.e. errors generated by child processes of the AbstractAction subclass instance,
     * wrapped by this ActionException instance.
     * @param string $message The exception message.
     * @param string[] $parameters The values to replace in the error message.
     *
     * @return ValidationException
     */
    public function getValidationExceptionWithChildren(
        AbstractAction $action,
        array $childFailures,
        string $message,
        string ...$parameters
    ): ValidationException
    {
        $actionException = $this->getValidationException($action, $message, ...$parameters);
        foreach ($childFailures as $childFailure) {
            if ($childFailure instanceof WorkerException) {
                $actionException->addChildFailure($childFailure);
            }
        }
        return $actionException;
    }
}