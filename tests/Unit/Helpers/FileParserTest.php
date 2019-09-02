<?php

namespace Tests\Unit\Helpers;

use Forte\Worker\Helpers\FileParser;
use PHPUnit\Framework\TestCase;

/**
 * Class FileParserTest
 *
 * @package Tests\Unit\Helpers
 */
class FileParserTest extends TestCase
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
            [__DIR__ . "/data/simple-config.ini", FileParser::CONTENT_TYPE_INI, $expectedArray],
            [__DIR__ . "/data/simple-config.json", FileParser::CONTENT_TYPE_JSON, $expectedArray],
            [__DIR__ . "/data/simple-config.php", FileParser::CONTENT_TYPE_ARRAY, $expectedArray],
            [__DIR__ . "/data/simple-config.xml", FileParser::CONTENT_TYPE_XML, $expectedArray],
            [__DIR__ . "/data/simple-config.yml", FileParser::CONTENT_TYPE_YAML, $expectedArray],
        ];
    }

    /**
     * Tests the FileParser::parseConfigFile() and FileParser::writeToConfigFile() functions.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param string $contentType
     * @param array $content
     *
     * @throws \Forte\Worker\Exceptions\GeneratorException
     */
    public function testWriteAndParseConfigFile(string $filePath, string $contentType, array $content): void
    {
        FileParser::writeToConfigFile($content, $filePath, $contentType);
        $parsedArray = FileParser::parseConfigFile($filePath, $contentType);
        $this->assertEquals($content, $parsedArray);
    }

    /**
     * Checks the supported list of content type.
     *
     * @throws \ReflectionException
     */
    public function testSupportedContentTypes(): void
    {
        $constants = FileParser::getSupportedContentTypes('CONTENT_TYPE');
        $this->assertIsArray($constants);
        $this->assertCount(5, $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_JSON', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_INI', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_YAML', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_XML', $constants);
        $this->assertArrayHasKey('CONTENT_TYPE_ARRAY', $constants);
    }
}
