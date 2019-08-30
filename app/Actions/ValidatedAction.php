<?php
/**
 * This file is part of the ForteFramework package.
 *
 * Copyright (c) 2019  Marco Spallanzani <marco@forteframework.com>
 *
 *  For the full copyright and license information,
 *  please view the LICENSE file that was distributed
 *  with this source code.
 */

namespace Forte\Api\Generator\Actions;

use Forte\Api\Generator\Exceptions\GeneratorException;

/**
 * Interface ValidatedAction. Basic behaviour of all classes that
 * perform an action, which needs a pre-run validation.
 *
 * @package Forte\Api\Generator\Actions
 */
interface ValidatedAction
{
    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if the implementing class instance
     * was well configured; false otherwise.
     *
     * @throws GeneratorException If the implementing class
     * instance was not well configured.
     */
    public function isValid(): bool;

    /**
     * Run the action.
     *
     * @return bool True if the implementing class instance
     * action was successfully run; false otherwise.
     *
     * @throws GeneratorException If the action was successfully run.
     */
    public function run(): bool;

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string;
}