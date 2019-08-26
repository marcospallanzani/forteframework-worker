<?php

namespace Forte\Api\Generator\Transformers\Transforms;

use Forte\Api\Generator\Exceptions\TransformException;

/**
 * Class AbstractTransform
 *
 * @package Forte\Api\Generator\Transformers\Transforms
 *
 * //TODO consider converting this class to an interface
 */
abstract class AbstractTransform
{
    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool
     *
     * @throws TransformException
     */
    public abstract function isValid(): bool;

    /**
     * Apply the transformation.
     *
     * @return bool
     *
     * @throws TransformException
     */
    public abstract function transform(): bool;
}