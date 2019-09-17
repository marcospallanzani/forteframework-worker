<?php

namespace Forte\Worker\Readers;

use Forte\Stdlib\ArrayableInterface;
use Forte\Worker\Exceptions\MissingKeyException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\Collection;
use Forte\Worker\Helpers\FileParser;

/**
 * Class ConfigFile.
 *
 * @package Forte\Worker\Readers
 */
class ConfigFile implements ArrayableInterface
{
    /**
     * @var array
     */
    protected $configEntries = [];

    /**
     * @var string
     */
    protected $configFilePath;

    /**
     * ConfigFile constructor.
     *
     * @param string $configFilePath The config file to load.
     * @param string $contentType The config file type (accepted values
     * are the FileParser constants starting with "CONTENT_TYPE").
     *
     * @throws WorkerException
     */
    public function __construct(string $configFilePath, string $contentType)
    {
        $this->configFilePath = $configFilePath;
        $this->configEntries = FileParser::parseFile($configFilePath, $contentType);
    }

    /**
     * Return the API configuration value for the given key;
     * if not defined, an error will be thrown.
     *
     * @param string $key The configuration key.
     *
     * @return mixed The value found for the given key.
     *
     * @throws MissingKeyException The key was not found.
     */
    public function getRequiredParameter(string $key)
    {
        return Collection::getRequiredNestedArrayValue($key, $this->configEntries);
    }

    /**
     * Return an array representation of this AbstractAction subclass instance.
     *
     * @return array An array representation of this AbstractAction subclass instance.
     */
    public function toArray(): array
    {
        return Collection::variablesToArray(get_object_vars($this));
    }

    /**
     * Return the original config file path.
     *
     * @return string
     */
    public function getConfigFilePath(): string
    {
        return $this->configFilePath;
    }
}
