<?php

namespace Forte\Worker\Checkers\Checks;

use Forte\Worker\Actions\ValidActionInterface;
use Forte\Worker\Exceptions\CheckException;
use Forte\Worker\Exceptions\GeneratorException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\FileTrait;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class AbstractCheck
 *
 * @package Forte\Worker\Checkers\Checks
 */
abstract class AbstractCheck implements ValidActionInterface
{
    use ClassAccessTrait, FileTrait, ThrowErrorsTrait;

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
