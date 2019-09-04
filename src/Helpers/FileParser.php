<?php

namespace Forte\Worker\Helpers;

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Exceptions\MissingKeyException;
use Symfony\Component\Yaml\Yaml as YamlReader;
use Zend\Config\Exception\RuntimeException;
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
     * The separator used in multi-level array keys.
     *
     * e.g. The multi-level key "level-1.level-2.final-level"
     * corresponds to the following array:
     * [
     *    'level-1' => [
     *        'level-2' => 'final-level',
     *     ],
     * ]
     */
    const ARRAY_KEYS_LEVEL_SEPARATOR = ".";

    /**
     * Supported content types.
     */
    const CONTENT_TYPE_JSON  = "content_json";
    const CONTENT_TYPE_INI   = "content_ini";
    const CONTENT_TYPE_YAML  = "content_yaml";
    const CONTENT_TYPE_XML   = "content_xml";
    const CONTENT_TYPE_ARRAY = "content_array";

    /**
     * Parse the given file path and return its content as an array.
     *
     * @param string $filePath The file to be parsed.
     * @param string $contentType The content type (supported types are the
     * constants whose name starts with the prefix 'CONTENT_TYPE').
     *
     * @return array An array representing the given file path.
     *
     * @throws WorkerException If an error occurred while parsing the file
     * (e.g. json syntax not respected).
     */
    public static function parseFile(string $filePath, string $contentType): array
    {
        try {
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

            if (is_array($parsedContent)) {
                return $parsedContent;
            }
            return [];
        } catch (RuntimeException $runtimeException) {
            throw new WorkerException(sprintf(
                "An error occurred while parsing the given file '%s' and content '%s'. Error message is: '%s'.",
                $filePath,
                $contentType,
                $runtimeException
            ));
        }
    }

    /**
     * Write the given content to the specified file.
     *
     * @param mixed $content The content to be written.
     * @param string $filePath The file to be changed.
     * @param string $contentType The content type (supported types are the
     * constants whose name starts with the prefix 'CONTENT_TYPE').
     *
     * @throws WorkerException
     */
    public static function writeToFile($content, string $filePath, string $contentType): void
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
            throw new WorkerException(sprintf(
                "It was not possible to save the given content to the specified file '%s'. Error message is: '%s",
                $filePath,
                $exception->getMessage()
            ));
        }
    }

    /**
     * Return the array value for the given key;
     * if not defined, an error will be thrown.
     *
     * @param string $key The multi-level array key.
     * @param array $array The array to access with
     * the given multi-level key.
     *
     * @return mixed
     *
     * @throws MissingKeyException
     */
    public static function getRequiredNestedArrayValue(string $key, array $array)
    {
        $keysTree = explode(self::ARRAY_KEYS_LEVEL_SEPARATOR, $key, 2);
        $value = null;
        if (count($keysTree) <= 2) {
            // We check if a value for the current array key exists;
            // If it does not exist, we throw an error.
            $currentKey = $keysTree[0];
            if (array_key_exists($currentKey, $array)) {
                $value = $array[$currentKey];
            } else {
                throw new MissingKeyException($key, "Array key '$key' not found.");
            }

            try {
                // If a value for the current key was found, we check
                // if we need to iterate again through the given array;
                if (count($keysTree) === 2) {
                    if (is_array($value)) {
                        $value = self::getRequiredNestedArrayValue($keysTree[1], $value);
                    } else {
                        throw new MissingKeyException($keysTree[1], "Array key '$keysTree[1]' not found.");
                    }
                }
            } catch (MissingKeyException $e) {
                $composedKey = $currentKey . self::ARRAY_KEYS_LEVEL_SEPARATOR . $e->getMissingKey();
                throw new MissingKeyException($composedKey, "Array key '$composedKey' not found.");
            }
        }
        return $value;
    }

    /**
     * Return an array containing all supported content types
     * (class constants with prefix 'CONTENT_TYPE').
     *
     * @return array An array of supported content types.
     */
    public static function getSupportedContentTypes(): array
    {
        return self::getClassConstants('CONTENT_TYPE');
    }
}
