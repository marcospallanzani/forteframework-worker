<?php

namespace Forte\Api\Generator\Config;

use Forte\Api\Generator\Exceptions\MissingConfigKeyException;
use Forte\Api\Generator\Exceptions\WrongConfigException;

/**
 * Class Reader.
 *
 * Class in charge of reading a config array and perform different operations on it.
 *
 * @package Forte\Api\Generator\Config
 */
class Reader
{
    // The separator used in multi-level configuration keys (e.g. "config-level-1.config-level-2.config-final-level")
    const CONFIG_LEVEL_SEPARATOR = ".";

    /**
     * Returns the configuration value for the given key;
     * if not defined, an error will be thrown.
     *
     * @param string $key The configuration key
     * @param array $config The config array to use;
     *
     * @return mixed
     *
     * @throws MissingConfigKeyException
     * @throws WrongConfigException
     */
    public static function getRequiredNestedConfigValue(string $key, array $config)
    {
        $keysTree = explode(self::CONFIG_LEVEL_SEPARATOR, $key, 2);
        $value = null;
        if (count($keysTree) <= 2) {
            // We check if a value for the current configuration key exists;
            // If it does not exist, we throw an error.
            $currentKey = $keysTree[0];
            if (array_key_exists($currentKey, $config)) {
                $value = $config[$currentKey];
            } else {
                throw new MissingConfigKeyException($key, "Configuration key '$key' not found.'");
            }

            try {
                // If a value for the current key was found, we check
                // if we need to iterate again throught the given config tree;
                if (count($keysTree) === 2) {
                    if(is_array($value)) {
                        $value = self::getRequiredNestedConfigValue($keysTree[1], $value);
                    } else {
                        throw new WrongConfigException(
                            $key,
                            "The value associated to the middle-level configuration key '$key' should be an array"
                        );
                    }
                }
            } catch (MissingConfigKeyException $e) {
                $composedKey = $currentKey . self::CONFIG_LEVEL_SEPARATOR . $e->getMissingKey();
                throw new MissingConfigKeyException($composedKey, "Configuration key '$composedKey' not found.'");
            }
        }
        return $value;
    }
}