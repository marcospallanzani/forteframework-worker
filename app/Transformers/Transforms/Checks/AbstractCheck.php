<?php

namespace Forte\Api\Generator\Transformers\Transforms\Checks;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Helpers\ClassConstantsTrait;
use Forte\Api\Generator\Helpers\FileTrait;
use Forte\Api\Generator\Helpers\ThrowsErrors;

/**
 * Class AbstractCheck
 *
 * @package Forte\Api\Generator\Transformers\Transforms\Checks
 */
abstract class AbstractCheck
{
    use ClassConstantsTrait, FileTrait, ThrowsErrors;

    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this AbstractCheck subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws CheckException
     */
    public abstract function isValid(): bool;

    /**
     * Apply the check.
     *
     * @return bool Returns true if this AbstractCheck subclass
     * instance check has been successfully; false otherwise.
     *
     * @throws CheckException
     */
    public abstract function check(): bool;

    /**
     * Returns a string representation of this AbstractCheck subclass instance.
     *
     * @return string
     */
    public abstract function stringify(): string;

    /**
     * Returns a string representation of this AbstractCheck subclass instance.
     *
     * @return false|string
     */
    public function __toString()
    {
        return static::stringify();
    }
}