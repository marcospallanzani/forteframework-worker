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
 * Interface ActionInterface. Basic behaviour of all classes that perform a
 * given action.
 *
 * @package Forte\Worker\Actions
 */
interface ActionInterface
{
    /**
     * None of the possible results raise an error; errors at execution time
     * are caught and change the action result to its negative case.
     */
    const EXECUTION_SEVERITY_NON_CRITICAL = 0;

    /**
     * Case EXECUTION_SEVERITY_NON_CRITICAL plus the following scenarios:
     * - a negative result change the global action result to its negative case;
     * - errors at execution time are caught and change the action result to its
     *   negative case;
     */
    const EXECUTION_SEVERITY_SUCCESS_REQUIRED = 1;

    /**
     * Case EXECUTION_SEVERITY_SUCCESS_REQUIRED plus the following scenario:
     * - errors at execution time are raised.
     */
    const EXECUTION_SEVERITY_FATAL = 2;

    /**
     * Case EXECUTION_SEVERITY_SUCCESS_REQUIRED plus the following scenario:
     * - a negative result raises an error;
     */
    const EXECUTION_SEVERITY_CRITICAL = 3;

    /**
     * Run the action.
     *
     * @return ActionResult The ActionResult instance representing the result
     * of the just-run action.
     *
     * @throws ActionException If the action was not successfully run.
     */
    public function run(): ActionResult;

    /**
     * Return a human-readable string representation of this implementing class
     * instance.
     *
     * @return string A human-readable string representation of this implementing
     * class instance.
     */
    public function stringify(): string;

    /**
     * Return the action severity of this implementing class instance.
     *
     * @return int The action severity.
     */
    public function getActionSeverity(): int;
}
