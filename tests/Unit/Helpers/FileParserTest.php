<?php

namespace Tests\Unit\Helpers;

use Forte\Worker\Exceptions\MissingKeyException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\FileParser;
use PHPUnit\Framework\TestCase;

/**
 * Class FileParserTest.
 *
 * @package Tests\Unit\Helpers
 */
class FileParserTest extends TestCase
{
    /**
     * A general test array.
     *
     * @var array
     */
    protected $testArray = [
        'test1' => [
            'test2' => 'value2'
        ],
        'test5' => [
            'test6' => [
                'test7' => [
                    'test8' => [
                        'test9' => [
                            'test10' => 'value10'
                        ]
                    ]
                ],
            ]
        ],
    ];

    /**
     * Data provider for all config access tests.
     *
     * @return array
     */
    public function configProvider(): array
    {
        return [
            //  access key | content to be checked | expected value for the given key | an exception is expected
            ['test1', $this->testArray, ['test2' => 'value2'], false],
            ['WRONG-KEY', $this->testArray, ['test2' => 'value2'], true],
            ['test1.test2', $this->testArray, 'value2', false],
            ['test1.WRONG-KEY', $this->testArray, 'value2', true],
            ['test5.test6.test7.test8.test9', $this->testArray, ['test10' => 'value10'], false],
            ['test5.test6.test7.WRONG-KEY.test9', $this->testArray, ['test10' => 'value10'], true],
        ];
    }

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
     * Test the method FileParser::getRequiredNestedConfigValue().
     *
     * @dataProvider configProvider
     *
     * @param string $key
     * @param array $checkContent
     * @param mixed $expectedValue
     * @param bool $expectException
     *
     * @throws MissingKeyException
     */
    public function testRequiredNestedConfigValue(
        string $key,
        array $checkContent,
        $expectedValue,
        bool $expectException
    ): void
    {
        if ($expectException) {
            $this->expectException(MissingKeyException::class);
        }
        $this->assertEquals($expectedValue, FileParser::getRequiredNestedArrayValue($key, $checkContent));
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
}
