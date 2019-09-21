<?php

namespace Forte\Worker\Actions;

/**
 * Class AbstractFileAction.
 *
 * @package Forte\Worker\Actions
 */
abstract class AbstractFileAction extends AbstractAction
{
    /**
     * Set the path required by the implementing class.
     *
     * @param string $path The path to be set.
     */
    public abstract function path(string $path);
}