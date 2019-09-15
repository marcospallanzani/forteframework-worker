<?php

namespace Forte\Worker\Exceptions;

use Forte\Worker\Actions\AbstractAction;

/**
 * Class ConfigurationException.
 *
 * @package Forte\Worker\Exceptions
 */
class ConfigurationException extends WorkerException
{
    /**
     * The AbstractAction subclass instance that generated this exception.
     *
     * @var AbstractAction
     */
    protected $action;

    /**
     * ConfigurationException constructor.
     *
     * @param AbstractAction $action The AbstractAction subclass instance
     * that generated the error.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previous error.
     */
    public function __construct(
        AbstractAction $action,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->action = $action;
    }

    /**
     * Return an array representation of this ConfigurationException instance.
     *
     * @return array Array representation of this ConfigurationException instance.
     */
    public function toArray(): array
    {
        $array = [];

        // The action
        $array['action'] = $this->action->stringify();

        // The error message
        $array['error_message'] = $this->message;

        // The error code
        $array['error_code'] = $this->code;

        return $array;
    }
}