<?php

namespace Forte\Worker\Runners;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
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
     * returns a list of ActionResult instances, where each entry
     * represents the result of each run action.
     *
     * @return array A list of ActionResult instances, where each entry
     * represents the result of each run action.
     *
     * @throws ActionException A critical error was found.
     */
    public function applyActions(): array
    {
        $actionResults = array();
        foreach ($this->actions as $action) {
            $actionResult = new ActionResult($action);
            try {
                if ($action instanceof AbstractAction) {
                    $actionResult = $action->run();
                }
            } catch (ActionException $actionException) {

                // If failure is critical (i.e. fatal or success required), we throw it again
                if ($actionException->hasCriticalFailures()) {
                    throw $actionException;
                }

                // If not critical, we add the failure to the current result
                $actionResult->addActionFailure($actionException);
            }

            // We add the current action result to the global list of action results
            $actionResults[] = $actionResult;

        }

        return $actionResults;
    }
}
