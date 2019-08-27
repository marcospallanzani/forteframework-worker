<?php

namespace Forte\Api\Generator\Transformers\Transforms;


use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;

/**
 * Class EmptyTransform. This class does not apply any transformation
 * and can be used as a support to run pre- and/or post-transform checks.
 *
 * @package Forte\Api\Generator\Transformers\Transforms
 */
class EmptyTransform extends AbstractTransform
{
    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws TransformException
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Apply the transformation.
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance has been successfully applied; false otherwise.
     *
     * @throws TransformException
     * @throws CheckException
     * @throws GeneratorException
     */
    public function transform(): bool
    {
        if ($this->isValid()) {
            // We run the pre-transform checks
            $this->runAndReportBeforeChecks(true);

            // We run the post-transform checks
            $this->runAndReportAfterChecks(true);
        }
        return true;
    }

    /**
     * Returns a string representation of this AbstractTransform subclass instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return "Empty transform";
    }
}
