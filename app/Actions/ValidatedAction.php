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
 * perform an action, which needs to be valid before being run.
 *
 * @package Forte\Api\Generator\Actions
 */
interface ValidatedAction
{
    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool Returns true if the implementing class
     * instance is well configured; false otherwise.
     *
     * @throws GeneratorException
     */
    public function isValid(): bool;

    /**
     * Run the action with pre-validation checks.
     *
     * @return bool Returns true if the implementing class instance
     * action has been successfully run; false otherwise.
     *
     * @throws GeneratorException
     */
    public function run(): bool;
}