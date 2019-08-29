<?php

namespace Forte\Api\Generator\Helpers;

use Forte\Api\Generator\Exceptions\MissingConfigKeyException;
use Forte\Api\Generator\Exceptions\WrongConfigException;
use Symfony\Component\Yaml\Yaml;
use Zend\Config\Reader\Ini;
use Zend\Config\Reader\Json;
use Zend\Config\Reader\Xml;

/**
 * Class FileParser
 *
 * @package Forte\Api\Generator\Helpers
 */
class FileParser
{
    use ClassAccessTrait;

    /**
     * The separator used in multi-level configuration keys
     * (e.g. "config-level-1.config-level-2.config-final-level")
     */
    const CONFIG_LEVEL_SEPARATOR = ".";

    /**
     * Supported content types.
     */
    const CONTENT_TYPE_JSON  = "content_json";
    const CONTENT_TYPE_INI   = "content_ini";
    const CONTENT_TYPE_YAML  = "content_yaml";
    const CONTENT_TYPE_XML   = "content_xml";
    const CONTENT_TYPE_ARRAY = "content_array";

    /**
     * Parses the given file path and return its content as an array.
     *
     * @param string $filePath The file to be parsed.
     * @param string $contentType The content type (supported types are the
     * constants whose name starts with the prefix 'CONTENT_TYPE').
     *
     * @return array An array representing the given file path.
     */
    public static function parseConfigFile(string $filePath, string $contentType): array
    {
        $parsedContent = null;
        switch ($contentType) {
            case self::CONTENT_TYPE_INI:
                $iniReader = new Ini();
                $parsedContent = $iniReader->fromFile($filePath);
                break;
            case self::CONTENT_TYPE_YAML:
                $parsedContent = Yaml::parseFile($filePath);
                break;
            case self::CONTENT_TYPE_JSON:
                $jsonReader = new Json();
                $parsedContent = $jsonReader->fromFile($filePath);
                break;
            case self::CONTENT_TYPE_XML:
                $xmlReader = new Xml();
                $parsedContent = $xmlReader->fromFile($filePath);
                break;
            case self::CONTENT_TYPE_ARRAY:
                $parsedContent = include ($filePath);
                break;
        }

        return $parsedContent;
    }

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
                throw new MissingConfigKeyException($key, "Configuration key '$key' not found.");
            }

            try {
                // If a value for the current key was found, we check
                // if we need to iterate again through the given config tree;
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
                throw new MissingConfigKeyException($composedKey, "Configuration key '$composedKey' not found.");
            }
        }
        return $value;
    }

    /**
     * Returns an array containing all supported content types
     * (class constants with prefix 'CONTENT_TYPE').
     *
     * @return array An array of supported content types.
     *
     * @throws \ReflectionException
     */
    public static function getSupportedContentTypes(): array
    {
        return self::getClassConstants('CONTENT_TYPE');
    }
}