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

namespace Forte\Api\Generator\Checkers\Checks;

/**
 * Class DirectoryExists.
 *
 * @package Forte\Api\Generator\Checkers\Checks
 */
class DirectoryExists extends FileExists
{
    /**
     * Return a human-readable string representation of this DirectoryExists instance.
     *
     * @return string A human-readable string representation of this DirectoryExists
     * instance.
     */
    public function stringify(): string
    {
        return "Check if directory '" . $this->filePath . "' exists.";
    }
}