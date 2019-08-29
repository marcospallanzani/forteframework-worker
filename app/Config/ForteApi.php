<?php

namespace Forte\Api\Generator\Config;

use Forte\Api\Generator\Exceptions\MissingConfigKeyException;
use Forte\Api\Generator\Exceptions\WrongConfigException;
use Forte\Api\Generator\Helpers\FileParser;

/**
 * Class ForteApi.
 *
 * This class wraps all required configuration keys, in order
 * to generate a new Forte API from the Forte API Skeleton.
 *
 * @package Forte\Api\Generator\Config
 */
class ForteApi
{
    /**
     * API Config Keys Constants
     */
    const CONFIG_PROJECT_NAME        = "name";
    const CONFIG_PROJECT_DESCRIPTION = "description";

    /**
     * The generation parameters.
     *
     * @var array
     */
    protected $config = array();

    /**
     * The project name (e.g. "Your Company API").
     *
     * @var string
     */
    protected $projectName;

    /**
     * The project description (e.g."Your Company API Description with more details").
     *
     * @var string
     */
    protected $projectDescription;

    /**
     * ForteApi constructor.
     *
     * @param array $config List of all required and optional parameters required for the API generation
     *
     * @throws MissingConfigKeyException
     * @throws WrongConfigException
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->projectName        = $this->getRequiredParameter(self::CONFIG_PROJECT_NAME);
        $this->projectDescription = $this->getRequiredParameter(self::CONFIG_PROJECT_DESCRIPTION);
    }

    /**
     * Returns the API configuration value for the given key;
     * if not defined, an error will be thrown;
     *
     * @param string $key The configuration key
     *
     * @return mixed
     *
     * @throws MissingConfigKeyException
     * @throws WrongConfigException
     */
    public function getRequiredParameter(string $key)
    {
        return FileParser::getRequiredNestedConfigValue($key, $this->config);
    }
}
