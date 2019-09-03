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

namespace Forte\Worker\Actions\Checks\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Exceptions\ActionException;

/**
 * Class FileExists.
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class FileExists extends AbstractAction
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * FileExists constructor.
     *
     * @param string $filePath The file path to check.
     */
    public function __construct(string $filePath = "")
    {
        $this->filePath = $filePath;
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if this FileExists instance
     * was well configured; false otherwise.
     *
     * @throws ActionException
     */
    public function isValid(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwActionException($this, "You must specify the file path.");
        }

        return true;
    }

    /**
     * Run the check.
     *
     * @return bool True if this FileExists instance
     * check was successful; false otherwise.
     *
     * @throws ActionException
     */
    protected function apply(): bool
    {
        // We check if the origin file exists
        return $this->checkFileExists($this->filePath, false);
    }

    /**
     * Set the file path for this FileExists instance.
     *
     * @param string $filePath The file path to check
     *
     * @return FileExists
     */
    public function setPath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Return a human-readable string representation of this FileExists instance.
     *
     * @return string A human-readable string representation of this FileExists
     * instance.
     */
    public function stringify(): string
    {
        return "Check if file '" . $this->filePath . "' exists.";
    }
}
