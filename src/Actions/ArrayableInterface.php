<?php


namespace Forte\Worker\Actions;


interface ArrayableInterface
{
    /**
     * Return an array representation of the implementing instance.
     *
     * @return array
     */
    public function toArray(): array;
}
