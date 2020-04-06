<?php

namespace Forte\Worker\Actions\Factories;

use Forte\Worker\Actions\AbstractAction;

/**
 * Interface ActionFactoryInterface.
 *
 * @package Forte\Worker\Actions\Factories
 */
interface ActionFactoryInterface
{
    /**
     * Create an instance of the given Abstract subclass name (full namespace).
     *
     * @param string $class The AbstractAction subclass name to be created (full namespace).
     * @param array $parameters The parameters required to generate the desired given action instance
     *
     * @return AbstractAction An instance of the required AbstractAction subclass.
     */
    public static function create(string $class, ...$parameters): AbstractAction;
}
