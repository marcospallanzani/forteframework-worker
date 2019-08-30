<?php

namespace Forte\Api\Generator\Checkers\Checks;

use Forte\Api\Generator\Actions\ValidatedAction;
use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Helpers\ClassAccessTrait;
use Forte\Api\Generator\Helpers\FileTrait;
use Forte\Api\Generator\Helpers\ThrowErrors;

/**
 * Class AbstractCheck
 *
 * @package Forte\Api\Generator\Checkers\Checks
 */
abstract class AbstractCheck implements ValidatedAction
{
    use ClassAccessTrait, FileTrait, ThrowErrors;

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if this AbstractCheck subclass instance
     * was well configured; false otherwise.
     *
     * @throws CheckException If this AbstractCheck subclass instance
     * was not well configured.
     */
    public abstract function isValid(): bool;

    /**
     * Run the check.
     *
     * @return bool True if this AbstractCheck subclass instance
     * ran successfully; false otherwise.
     *
     * @throws CheckException If this AbstractCheck subclass instance
     * check did not run successfully.
     */
    protected abstract function check(): bool;

    /**
     * Run the action.
     *
     * @return bool True if this AbstractCheck subclass instance
     * action ran successfully; false otherwise.
     *
     * @throws GeneratorException If this AbstractCheck subclass instance
     * action did not run successfully.
     */
    public function run(): bool
    {
        if ($this->isValid()) {
            return $this->check();
        }

        return false;
    }

    /**
     * Return a string representation of this AbstractCheck subclass instance.
     *
     * @return false|string A string representation of this AbstractCheck
     * subclass instance.
     */
    public function __toString()
    {
        return static::stringify();
    }
}
