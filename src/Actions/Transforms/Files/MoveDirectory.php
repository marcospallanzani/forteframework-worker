<?php

namespace Forte\Worker\Actions\Transforms\Files;

/**
 * Class MoveDirectory.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class MoveDirectory extends MoveFile
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
            "Move directory '%s' to '%s'.",
            $this->sourcePath,
            $this->targetPath
        );
    }
}