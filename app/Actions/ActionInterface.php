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
 * Interface ActionInterface. Basic behaviour of all classes that
 * perform a given action.
 *
 * @package Forte\Api\Generator\Actions
 */
interface ActionInterface
{
    /**
     * Run the action.
     *
     * @return bool True if the implementing class instance
     * action was successfully run; false otherwise.
     *
     * @throws GeneratorException If the action was not successfully run.
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