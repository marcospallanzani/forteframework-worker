<?php

namespace Tests\Unit\Helpers;

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\FileParser;
use Tests\Unit\BaseTest;

/**
 * Class FileParserTest.
 *
 * @package Tests\Unit\Helpers
 */
class FileParserTest extends BaseTest
{
    /**
     * Data provider for parse tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        $now = time();
        $expectedArray = [
            "key1$now" => "value1$now",
            "key2$now" => [
                "key3$now" => "value3$now",
                "key4$now" => [
                    "key5$now" => "value5$now"
                ]
            ]
        ];

        return [
            // file path    |   content type    |   content     |       expect an exception
            [__DIR__ . "/data/simple-config.ini", FileParser::CONTENT_TYPE_INI, $expectedArray, false],
            [__DIR__ . "/data/simple-config.json", FileParser::CONTENT_TYPE_JSON, $expectedArray, false],
            [__DIR__ . "/data/simple-config.php", FileParser::CONTENT_TYPE_ARRAY, $expectedArray, false],
            [__DIR__ . "/data/simple-config.xml", FileParser::CONTENT_TYPE_XML, $expectedArray, false],
            [__DIR__ . "/data/simple-config.yml", FileParser::CONTENT_TYPE_YAML, $expectedArray, false],
            ["", FileParser::CONTENT_TYPE_INI, $expectedArray, true],
            ["", FileParser::CONTENT_TYPE_JSON, $expectedArray, true],
            [__DIR__ . "", FileParser::CONTENT_TYPE_ARRAY, $expectedArray, true],
            [__DIR__ . "", FileParser::CONTENT_TYPE_XML, $expectedArray, true],
            [__DIR__ . "/data/simple-config", 'text', [], false],
        ];
    }

    /**
     * @return array
     */
    public function emptyFilesProvider(): array
    {
        return [
            [__DIR__ . '/data/empty_parsetest.json', FileParser::CONTENT_TYPE_JSON],
            [__DIR__ . '/data/empty_parsetest.json', FileParser::CONTENT_TYPE_XML],
        ];
    }

    /**
     * Test the FileParser::parseConfigFile() and FileParser::writeToConfigFile() functions.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param string $contentType
     * @param array $content
     * @param bool $expectException
     *
     * @throws WorkerException
     */
    public function testWriteAndParseConfigFile(
        string $filePath,
        string $contentType,
        array $content,
        bool $expectException
    ): void
    {
        if ($expectException) {
            $this->expectException(WorkerException::class);
        }
        FileParser::writeToFile($content, $filePath, $contentType);
        $parsedArray = FileParser::parseFile($filePath, $contentType);
        $this->assertEquals($content, $parsedArray);
    }

    /**
     * If an empty json file is parsed, a Runtime exception should be thrown.
     *
     * @dataProvider emptyFilesProvider
     *
     * @param string $filePath
     * @param string $contentType
     *
     * @throws WorkerException
     */
    public function testParseExpectRuntimeException(string $filePath, string $contentType): void
    {
        $this->expectException(WorkerException::class);
        FileParser::parseFile($filePath, $contentType);
    }

    /**
     * Check the supported list of content type.
     */
    public function testSupportedContentTypes(): void
    {
        $constants = FileParser::getSupportedContentTypes();
        $this->assertIsArray($constants);
        $this->assertCount(5, $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_JSON', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_INI', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_YAML', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_XML', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_ARRAY', $constants);
    }

    /**
     * Test function FileParser::getFileExtensionByContentType().
     */
    public function testFileExtensions(): void
    {
        $this->assertEquals('ini', FileParser::getFileExtensionByContentType(FileParser::CONTENT_TYPE_INI));
        $this->assertEquals('json', FileParser::getFileExtensionByContentType(FileParser::CONTENT_TYPE_JSON));
        $this->assertEquals('php', FileParser::getFileExtensionByContentType(FileParser::CONTENT_TYPE_ARRAY));
        $this->assertEquals('yml', FileParser::getFileExtensionByContentType(FileParser::CONTENT_TYPE_YAML));
        $this->assertEquals('xml', FileParser::getFileExtensionByContentType(FileParser::CONTENT_TYPE_XML));
        $this->assertEquals('', FileParser::getFileExtensionByContentType('wrong_content_type'));
    }
}
