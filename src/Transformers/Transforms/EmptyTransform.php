<?php

namespace Forte\Worker\Transformers\Transforms;

use Forte\Worker\Actions\AbstractAction;

/**
 * Class EmptyTransform. This class does not apply any transformation
 * and can be used as a support to run pre- and/or post-transform checks.
 *
 * @package Forte\Worker\Transformers\Transforms
 */
class EmptyTransform extends AbstractAction
{
    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if this EmptyTransform instance
     * was well configured; false otherwise.
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Apply the sub-class transformation action.
     *
     * @return bool True if the transform action implemented by this
     * EmptyTransform instance was successfully applied; false otherwise.
     */
    protected function apply(): bool
    {
        return true;
    }

    /**
     * Returns a string representation of this EmptyTransform instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return "Empty transform";
    }
}
