<?php

namespace Forte\Worker\Helpers;

use Forte\Stdlib\ClassAccessTrait;
use Forte\Worker\Exceptions\WorkerException;
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
     * @return bool True if the content was successfully written to the
     * given file path.
     *
     * @throws WorkerException
     */
    public static function writeToFile($content, string $filePath, string $contentType): bool
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

        return true;
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

    /**
     * Return a file extension for the given content type. The only content types
     * supported are the class constants starting with "CONTENT_TYPE_".
     *
     * @param string $contentType The file content type (supported content types
     * -> class constants starting "CONTENT_TYPE_" ).
     *
     * @return string The file extension for the given content type (only works with
     * supported content types -> class constants starting "CONTENT_TYPE_").
     */
    public static function getFileExtensionByContentType(string $contentType): string
    {
        $fileExtension = "";
        switch ($contentType) {
            case self::CONTENT_TYPE_INI:
                $fileExtension = "ini";
                break;
            case self::CONTENT_TYPE_YAML:
                $fileExtension = "yml";
                break;
            case self::CONTENT_TYPE_JSON:
                $fileExtension = "json";
                break;
            case self::CONTENT_TYPE_XML:
                $fileExtension = "xml";
                break;
            case self::CONTENT_TYPE_ARRAY:
                $fileExtension = "php";
                break;
        }

        return $fileExtension;
    }

    /**
     * Export the given array to the given destination full file path. If no destination
     * full file path is specified, a default path will be generated as follows:
     * - use the $defaultNamePrefix parameter concatenated with the execution timestamp
     *   to generate the destination file name;
     * - use the $exportDirPath parameter to define the export directory; if this parameter
     *   is empty, the execution directory will be used.
     *
     * @param array $content The array to write to the destination file.
     * @param string $contentType The file content type (accepted values are
     * FileParser constants starting with "CONTENT_TYPE_").
     * @param string $destinationFullFilePath The destination file path. If not given,
     * a default file name will be created.
     * @param string $defaultNamePrefix In case no destination file is specified,
     * this prefix will be used to generate a default file name (this prefix
     * concatenated with the execution timestamp).
     * @param string $exportDirPath In case no destination file is specified,
     * this field will be used to generated the default file name full path;
     * if empty, the execution directory will be used.
     *
     * @return string The export full file path.
     *
     * @throws WorkerException An error occurred while writing the
     * given array content to the export file.
     */
    public static function exportArrayReportToFile(
        array $content,
        string $contentType = self::CONTENT_TYPE_JSON,
        string $destinationFullFilePath = "",
        string $defaultNamePrefix = "export_data",
        string $exportDirPath = ""
    ): string
    {
        if (!empty($destinationFullFilePath) && is_dir($destinationFullFilePath)) {
            throw new WorkerException("The given destination file path cannot be a directory.");
        }

        if (empty($destinationFullFilePath)) {
            // We check the given parameters
            if (!empty($exportDirPath)) {
                $exportDirPath = rtrim($exportDirPath, DIRECTORY_SEPARATOR);
            } else {
                $exportDirPath = ".";
            }

            // We define a default name
            $fileName = rtrim($defaultNamePrefix, "_") . "_" . number_format(microtime(true), 12, '', '');
            $fileExtension = FileParser::getFileExtensionByContentType($contentType);
            if ($fileExtension) {
                $fileName .= '.' . $fileExtension;
            } else {
                // It means that the given content type is not supported by the FileParser class.
                // In this case, we set it by default to array.
                $contentType = self::CONTENT_TYPE_ARRAY;
                $fileName .= '.php';
            }
            $destinationFullFilePath = $exportDirPath . DIRECTORY_SEPARATOR . $fileName;
        }

        // If XML content type, we have to define a parent node name
        if ($contentType === FileParser::CONTENT_TYPE_XML) {
            $content['element'] = $content;
        }

        // We write the result to the file path
        self::writeToFile($content, $destinationFullFilePath, $contentType);

        return $destinationFullFilePath;
    }
}
