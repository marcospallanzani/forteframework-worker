<?php

namespace Forte\Worker\Exceptions;

use Forte\Worker\Actions\AbstractAction;

/**
 * Class ActionException.
 *
 * @package Forte\Worker\Exceptions
 */
class ActionException extends WorkerException
{
    /**
     * @var AbstractAction
     */
    protected $action;

    /**
     * An array of AbstractAction instances that are linked to the
     * main AbstractAction instance, as a pre- or post-run action.
     *
     * @var array
     */
    protected $actionDependencies = [];
//TODO SHOULD WE SAVE THE CHILDREN EXCEPTION? SO THAT WE WOULD HAVE A REFERENCE TO THE ACTION AND THE ERROR THAT THIS CHILD ACTION RAISED

    /**
     * ActionException constructor.
     *
     * @param AbstractAction $action The AbstractAction subclass
     * instance that generated the error.
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
     * Returns the AbstractAction subclass instance
     * that generated this error.
     *
     * @return AbstractAction
     */
    public function getAction(): AbstractAction
    {
        return $this->action;
    }
}
