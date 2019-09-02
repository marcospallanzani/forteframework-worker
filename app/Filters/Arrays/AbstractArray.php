<?php

namespace Forte\Worker\Filters\Arrays;

use Forte\Worker\Actions\ValidInterface;

/**
 * Class AbstractArray. General class for all arrays related filters.
 *
 * @package Forte\Worker\Filters\Arrays
 */
abstract class AbstractArray implements ValidInterface
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
    protected $action;

    /**
     * AbstractArray constructor.
     *
     * @param string $key The array key to access (multi-level keys separated by '.').
     * @param string $action The action to perform (look inside isValid() implementation
     * for list of supported values).
     * @param mixed  $value The value to set/change/remove.
     */
    public function __construct(string $key, string $action, $value = null)
    {
        $this->key    = $key;
        $this->action = $action;
        $this->value  = $value;
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
     * Returns the action.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
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
        return $this->getActionMessage();
    }

    /**
     * Returns a human-readable description of this action.
     *
     * @return string
     */
    public abstract function getActionMessage(): string;
}