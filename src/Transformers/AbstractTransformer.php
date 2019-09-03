<?php

namespace Forte\Worker\Transformers;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Exceptions\ActionException;

/**
 * Class AbstractTransformer. A base class for all transformer implementations.
 *
 * @package Forte\Worker\Transformers
 */
class AbstractTransformer
{
    /**
     * Actions to apply.
     *
     * @var array An array of AbstractAction subclass instances.
     */
    protected $actions = [];

    /**
     * Get all of the the required actions to apply.
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
     * Apply all configured transformations in the given sequence.
     * This method returns a list of AbstractAction subclass
     * that failed  or that did not execute correctly.
     *
     * @return array A list of AbstractAction subclass instances
     * that executed correctly, but failed.
     */
    public function applyActions(): array
    {
        $failedActions = array();
        foreach ($this->actions as $action) {
            try {
                if ($action instanceof AbstractAction && !$action->run()) {
                    $failedActions[] = $action;
                }
            } catch (ActionException $actionException) {
//TODO Error handling: how to return a chain of action exceptions?
                $failedActions[] = $actionException->getMessage();
            }
        }
        return $failedActions;
    }
}
