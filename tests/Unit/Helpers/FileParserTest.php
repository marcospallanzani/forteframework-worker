<?php

namespace Tests\Unit\Helpers;

use Forte\Api\Generator\Helpers\FileParser;
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
        $expectedArray = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => [
                    "key5" => "value5"
                ]
            ]
        ];

        return [
            [__DIR__ . '/data/parsetest.ini', FileParser::CONTENT_TYPE_INI, $expectedArray],
            [__DIR__ . '/data/parsetest.json', FileParser::CONTENT_TYPE_JSON, $expectedArray],
            [__DIR__ . '/data/parsetest.php', FileParser::CONTENT_TYPE_ARRAY, $expectedArray],
            [__DIR__ . '/data/parsetest.xml', FileParser::CONTENT_TYPE_XML, $expectedArray],
            [__DIR__ . '/data/parsetest.yml', FileParser::CONTENT_TYPE_YAML, $expectedArray],
        ];
    }

    /**
     * Tests the FileParser::parseConfigFile() function.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param string $contentType
     * @param array $expectedArray
     */
    public function testParseConfigFile(string $filePath, string $contentType, array $expectedArray): void
    {
        $parsedArray = FileParser::parseConfigFile($filePath, $contentType);
        $this->assertEquals($expectedArray, $parsedArray);
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
