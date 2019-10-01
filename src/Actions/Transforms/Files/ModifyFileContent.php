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

namespace Forte\Worker\Actions\Transforms\Files;

/**
 * Class ModifyFileContent. This class is used to modify the content of a given
 * file. This is a READ-ONLY version of the ModifyFile class, i.e. the file content
 * will be parsed and modified BUT NOT WRITTEN to the configured file.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class ModifyFileContent extends ModifyFile
{
    /**
     * Return a human-readable string representation of this
     * ModifyFile instance.
     *
     * @return string A human-readable string representation
     * of this ModifyFile instance.
     */
    public function stringify(): string
    {
        return str_replace(
            "Apply the following transformations to the specified file",
            "Apply the following transformations to the specified file content (READ-ONLY)",
            parent::stringify()
        );
    }

    /**
     * Write the given lines to the configured class file.
     *
     * @param array $modifiedContent All the lines to write to the configured class file.
     */
    protected function writeModifiedContent(array $modifiedContent): void
    {
        // In this child class of ModifyFile, we DO NOT WRITE THE MODIFIED CONTENT TO THE ORIGINAL FILE
        return;
    }
}
