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

use Forte\Worker\Actions\ActionResult;

/**
 * Class FileDoesNotExist.
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class FileDoesNotExist extends FileExists
{
    /**
     * Run the check.
     *
     * @param ActionResult $actionResult The action result object to register
     * all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields
     * regarding failures and result content.
     *
     * @throws \Exception
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        // We check if the origin file exists
        return $actionResult->setResult(!$this->fileExists($this->filePath, false));
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
