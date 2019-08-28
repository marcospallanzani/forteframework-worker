<?php

namespace Forte\Api\Generator\Checkers\Checks;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Helpers\ClassAccessTrait;
use Forte\Api\Generator\Helpers\FileTrait;
use Forte\Api\Generator\Helpers\ThrowErrors;

/**
 * Class AbstractCheck
 *
 * @package Forte\Api\Generator\Checkers\Checks
 */
abstract class AbstractCheck
{
    use ClassAccessTrait, FileTrait, ThrowErrors;

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
