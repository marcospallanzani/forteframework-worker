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
 * Interface ValidInterface. Basic behaviour of all classes need a validation.
 *
 * @package Forte\Api\Generator\Actions
 */
interface ValidInterface
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
}