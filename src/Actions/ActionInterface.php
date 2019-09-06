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

namespace Forte\Worker\Actions;

use Forte\Worker\Exceptions\ActionException;

/**
 * Interface ActionInterface. Basic behaviour of all classes that
 * perform a given action.
 *
 * @package Forte\Worker\Actions
 */
interface ActionInterface
{
    /**
     * Run the action.
     *
     * @return ActionResult The ActionResult instance representing
     * the result of the just-run action.
     *
     * @throws ActionException If the action was not successfully run.
     */
    public function run(): ActionResult;

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string;
}
