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
