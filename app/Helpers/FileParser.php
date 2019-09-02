<?php

namespace Forte\Worker\Helpers;

use Forte\Worker\Exceptions\GeneratorException;
use Forte\Worker\Exceptions\MissingKeyException;
use Symfony\Component\Yaml\Yaml as YamlReader;
use Zend\Config\Reader\Ini as IniReader;
use Zend\Config\Reader\Json as JsonReader;
use Zend\Config\Reader\Xml as XmlReader;
use Zend\Config\Writer\Ini as IniWriter;
use Zend\Config\Writer\Json as JsonWriter;
use Zend\Config\Writer\PhpArray;
use Zend\Config\Writer\Xml as XmlWriter;

/**
 * Class FileParser
 *
 * @package Forte\Worker\Helpers
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
                $iniReader = new IniReader();
                $parsedContent = $iniReader->fromFile($filePath);
                break;
            case self::CONTENT_TYPE_YAML:
                $parsedContent = YamlReader::parseFile($filePath);
                break;
            case self::CONTENT_TYPE_JSON:
                $jsonReader = new JsonReader();
                $parsedContent = $jsonReader->fromFile($filePath);
                break;
            case self::CONTENT_TYPE_XML:
                $xmlReader = new XmlReader();
                $parsedContent = $xmlReader->fromFile($filePath);
                break;
            case self::CONTENT_TYPE_ARRAY:
                $parsedContent = include ($filePath);
                break;
        }

        return $parsedContent;
    }

    /**
     * Write the given content to the specified file.
     *
     * @param mixed $content The content to be written.
     * @param string $filePath The file to be changed.
     * @param string $contentType The content type (supported types are the
     * constants whose name starts with the prefix 'CONTENT_TYPE').
     *
     * @throws GeneratorException
     */
    public static function writeToConfigFile($content, string $filePath, string $contentType): void
    {
        try {
            switch ($contentType) {
                case self::CONTENT_TYPE_INI:
                    $iniWriter = new IniWriter();
                    $iniWriter->toFile($filePath, $content);
                    break;
                case self::CONTENT_TYPE_YAML:
                    $ymlContent = YamlReader::dump($content);
                    file_put_contents($filePath, $ymlContent);
                    break;
                case self::CONTENT_TYPE_JSON:
                    $jsonWriter = new JsonWriter();
                    $jsonWriter->toFile($filePath, $content);
                    break;
                case self::CONTENT_TYPE_XML:
                    $xmlWriter = new XmlWriter();
                    $xmlWriter->toFile($filePath, $content);
                    break;
                case self::CONTENT_TYPE_ARRAY:
                    $phpWriter = new PhpArray();
                    $phpWriter->toFile($filePath, $content);
                    break;
            }
        } catch (\Exception $exception) {
            throw new GeneratorException(sprintf(
                "It was not possible to save the given content to the specified file '%s'. Error message is: '%s",
                $filePath,
                $exception->getMessage()
            ));
        }
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
     * @throws MissingKeyException
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
                throw new MissingKeyException($key, "Configuration key '$key' not found.");
            }

            try {
                // If a value for the current key was found, we check
                // if we need to iterate again through the given config tree;
                if (count($keysTree) === 2 && is_array($value)) {
                    $value = self::getRequiredNestedConfigValue($keysTree[1], $value);
                }
            } catch (MissingKeyException $e) {
                $composedKey = $currentKey . self::CONFIG_LEVEL_SEPARATOR . $e->getMissingKey();
                throw new MissingKeyException($composedKey, "Configuration key '$composedKey' not found.");
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