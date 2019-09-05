<?php

namespace Forte\Worker\Exceptions;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;

/**
 * Class ActionException.
 *
 * @package Forte\Worker\Exceptions
 */
class ActionException extends WorkerException
{
    /**
     * @var ActionResult
     */
    protected $actionResult;

    /**
     * A list of ActionResult instances that are linked to the main ActionResult
     * instance, as the result of a pre-, post-run or nested action.
     *
     * @var array
     */
    protected $failedChildrenActionResults = [];

    /**
     * ActionException constructor.
     *
     * @param AbstractAction $actionResult The ActionResult instance that represents the
     * run status of the AbstractAction subclass instance that generated the error.
     * @param string $message The exception message.
     * @param int $code The exception code.
     * @param \Throwable|null $previous The previous error.
     */
    public function __construct(
        ActionResult $actionResult,
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->actionResult = $actionResult;
    }

    /**
     * Returns the AbstractAction subclass instance
     * that generated this error.
     *
     * @return ActionResult
     */
    public function getActionResult(): ActionResult
    {
        return $this->actionResult;
    }

    /**
     * Return the list of failed children actions (i.e. pre- and
     * post-run actions of the current failed action).
     *
     * @return array List of failed children actions.
     */
    public function getFailedChildrenActionResults(): array
    {
        return $this->failedChildrenActionResults;
    }

    /**
     * Set the list of failed children actions (i.e. pre- and
     * post-run actions of the current failed action).
     *
     * @param array $failedChildrenActionResults List of failed children actions to be set.
     */
    public function setFailedChildrenActionResults(array $failedChildrenActionResults): void
    {
        $this->failedChildrenActionResults = $failedChildrenActionResults;
    }

    /**
     * Add the given ActionResult instance to the list of failed children action
     * results (i.e. pre-, post- or nested-run actions).
     *
     * @param ActionResult $actionResult
     */
    public function addFailedChildAction(ActionResult $actionResult): void
    {
        $this->failedChildrenActionResults[] = $actionResult;
    }

    /**
     * Check if some critical failures were found in the failures tree of the ActionResult
     * instance, associated to this ActionException.
     *
     * @return bool True if some critical failures were found in the failures tree of the
     * ActionResult instance, associated to this ActionException.
     */
    public function checkForCriticalFailures(): bool
    {
        return $this->actionResult->hasCriticalFailures();
    }
}
