<?php

namespace Forte\Worker\Actions\Transforms;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;

/**
 * Class EmptyTransform. This class does not apply any transformation
 * and can be used as a support to run pre- and/or post-transform checks.
 *
 * @package Forte\Worker\Actions\Transforms
 */
class EmptyTransform extends AbstractAction
{
    /**
     * Returns a string representation of this EmptyTransform instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return "Empty transform";
    }

    /**
     * Apply the sub-class transformation action.
     *
     * @param ActionResult $actionResult The action result object to register
     * all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields
     * regarding failures and result content.
     *
     * @throws \Exception
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        return $actionResult->setResult(true);
    }

    /**
     * Validate the given action result. This method returns true if the
     * given ActionResult instance has a result value that is considered
     * as a positive case by this AbstractAction subclass instance.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of the current AbstractAction subclass
     * instance.
     *
     * @return bool True if the given ActionResult instance has a result value
     * that is considered as a positive case by this AbstractAction subclass
     * instance; false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        // The ActionResult->result field should be set with a boolean
        // representing the last execution of the apply method.
        return (bool) $actionResult->getResult();
    }

    /**
     * Validate this AbstractAction subclass instance using a validation logic
     * specific to the current instance.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        return true;
    }
}
