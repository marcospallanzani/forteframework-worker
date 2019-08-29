<?php

namespace Forte\Api\Generator\Filters\Arrays;

/**
 * Class AbstractArray. General class for all arrays related filters.
 *
 * @package Forte\Api\Generator\Filters\Arrays
 */
abstract class AbstractArray
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * @var string
     */
    protected $operation;

    /**
     * ModifyArray constructor.
     *
     * @param string $key The array key to access (multi-level keys separated by '.').
     * @param string $operation The operation to perform (one of the class constants
     * starting with prefix 'MODIFY_').
     * @param mixed  $value The value to set/change/remove.
     */
    public function __construct(string $key, string $operation, $value = null)
    {
        $this->key       = $key;
        $this->operation = $operation;
        $this->value     = $value;
    }

    /**
     * Returns the key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the operation.
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * Returns a string version of the set value (it converts arrays to json).
     *
     * @return string
     */
    public function stringifyValue(): string
    {
        if (is_array($this->value)) {
            return json_encode($this->value);
        }
        return (string) $this->value;
    }

    /**
     * Returns a string representation of this VerifyArray instance.
     *
     * @return false|string
     */
    public function __toString()
    {
        return $this->getOperationMessage();
    }

    /**
     * Returns a human-readable description of this operation.
     *
     * @return string
     */
    protected abstract function getOperationMessage(): string;
}