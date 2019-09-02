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

namespace Forte\Worker\Checkers\Checks\Files;

use Forte\Worker\Exceptions\WorkerException;

/**
 * Class FileDoesNotExist.
 *
 * @package Forte\Worker\Checkers\Checks\Files
 */
class FileDoesNotExist extends FileExists
{
    /**
     * Run the check.
     *
     * @return bool True if this FileDoesNotExist instance
     * check was successful; false otherwise.
     *
     * @throws WorkerException
     */
    protected function check(): bool
    {
        // We check if the given file does not exist
        return !$this->checkFileExists($this->filePath, false);
    }

    /**
     * Return a human-readable string representation of this FileDoesNotExist instance.
     *
     * @return string A human-readable string representation of this FileExists
     * instance.
     */
    public function stringify(): string
    {
        return "Check if file '" . $this->filePath . "' does not exist.";
    }
}
