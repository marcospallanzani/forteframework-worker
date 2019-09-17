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

use Forte\Stdlib\ArrayableInterface;

/**
 * Interface ValidActionInterface. Basic behaviour of all classes that
 * perform an action, which needs a pre-run validation.
 *
 * @package Forte\Worker\Actions
 */
interface ValidActionInterface extends ValidInterface, ActionInterface, ArrayableInterface
{
    //
}
