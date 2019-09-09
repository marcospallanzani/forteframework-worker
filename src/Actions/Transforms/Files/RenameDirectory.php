<?php

namespace Forte\Worker\Actions\Transforms\Files;

/**
 * Class RenameDirectory. This class renames the given source directory with
 * the given target directory name. This action CANNOT MOVE the given source
 * directory to another directory.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class RenameDirectory extends RenameFile
{
    /**
     * Return a human-readable string representation of this
     * RenameDirectory instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        return sprintf(
            "Rename directory '%s' to '%s'.",
            $this->sourcePath,
            $this->targetName
        );
    }
}