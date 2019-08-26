<?php

namespace Forte\Api\Generator\Transformers;

use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;

/**
 * Class AbstractTransformer. A base class for all transformer implementations.
 *
 * @package Forte\Api\Generator\Transformers
 */
class AbstractTransformer
{
    /**
     * Transformations to apply.
     *
     * @var array An array of AbstractTransform subclass instances.
     */
    protected $transforms = [];

    /**
     * Get all of the the required transformations to apply.
     *
     * @return array An array of AbstractTransform subclass instances.
     */
    public function getTransforms(): array
    {
        return $this->transforms;
    }

    /**
     * Add a transformation to apply.
     *
     * @param AbstractTransform $transform
     */
    public function addTransform(AbstractTransform $transform)
    {
        $this->transforms[] = $transform;
    }

    /**
     * Apply all configured transformations in the given sequence.
     *
     * @throws \Forte\Api\Generator\Exceptions\TransformException
     */
    public function applyTransformations(): void
    {
        foreach ($this->transforms as $transform) {
            /** @var AbstractTransform $transform */
            $transform->transform();
        }
    }
}
