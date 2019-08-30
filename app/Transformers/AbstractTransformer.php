<?php

namespace Forte\Api\Generator\Transformers;

use Forte\Api\Generator\Exceptions\GeneratorException;
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
     * This method returns a list of AbstractTransform subclass
     * that failed  or that did not execute correctly.
     *
     * @return array A list of AbstractTransform subclass instances
     * that executed correctly, but failed.
     */
    public function applyTransformations(): array
    {
        $failedTransforms = array();
        foreach ($this->transforms as $transform) {
            try {
                if ($transform instanceof AbstractTransform && !$transform->run()) {
                    $failedTransforms[] = $transform;
                }
            } catch (GeneratorException $generatorException) {
                $failedTransforms[] = $generatorException->getMessage();
            }
        }
        return $failedTransforms;
    }
}
