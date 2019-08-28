<?php

namespace Forte\Api\Generator\Helpers;

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