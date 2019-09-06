<?php

namespace Forte\Worker\Runners;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Exceptions\ActionException;

/**
 * Class AbstractRunner. A base class for all runner implementations.
 *
 * @package Forte\Worker\Runners
 */
class AbstractRunner
{
    /**
     * Actions to apply.
     *
     * @var array An array of AbstractAction subclass instances.
     */
    protected $actions = [];

    /**
     * Get a list of actions to be applied.
     *
     * @return array An array of AbstractAction subclass instances.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Add an action to apply.
     *
     * @param AbstractAction $action
     */
    public function addAction(AbstractAction $action)
    {
        $this->actions[] = $action;
    }

    /**
     * Apply all configured actions in the given sequence. This method
     * returns a list of AbstractAction subclass instances that failed
     *  or that did not execute correctly.
     *
     * @return array A list of AbstractAction subclass instances
     * that failed.
     */
    public function applyActions(): array
    {
        $failedActions = array();
        foreach ($this->actions as $action) {
            try {
                if ($action instanceof AbstractAction) {
                    $actionResult = $action->run();
                    if (!$actionResult->isSuccessfulRun() || !$action->validateResult($actionResult)) {
                        $failedActions[] = $action;
                    }
                }
            } catch (ActionException $actionException) {
//TODO Error handling: how to return a chain of action exceptions?
                $failedActions[] = $actionException->getMessage();
            }
        }
        return $failedActions;
    }
}
