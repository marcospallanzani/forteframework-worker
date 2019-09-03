<?php

namespace Tests\Unit\Actions\Checks\Files;

use Forte\Worker\Actions\Checks\Files\FileHasValidEntries;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\FileParser;
use PHPUnit\Framework\TestCase;

/**
 * Class FileHasValidEntriesTest
 *
 * @package Tests\Unit\Actions\Checks\Files
 */
class FileHasValidEntriesTest extends TestCase
{
    /**
     * Temporary files constants
     */
    const TEST_FILE_TMP_JSON  = __DIR__ . '/file-tests.json';
    const TEST_FILE_TMP_INI   = __DIR__ . '/file-tests.ini';
    const TEST_FILE_TMP_YAML  = __DIR__ . '/file-tests.yml';
    const TEST_FILE_TMP_XML   = __DIR__ . '/file-tests.xml';
    const TEST_FILE_TMP_ARRAY = __DIR__ . '/file-tests.php';

    protected $testArray = [];

    /**
     * This method is called before each test.
     *
     * @throws WorkerException
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->testArray = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => [
                    "key5" => "value5"
                ]
            ],
            "key99" => ''
        ];

        FileParser::writeToFile($this->testArray, self::TEST_FILE_TMP_JSON, FileParser::CONTENT_TYPE_JSON);
        FileParser::writeToFile($this->testArray, self::TEST_FILE_TMP_ARRAY, FileParser::CONTENT_TYPE_ARRAY);
        FileParser::writeToFile($this->testArray, self::TEST_FILE_TMP_INI, FileParser::CONTENT_TYPE_INI);
        FileParser::writeToFile($this->testArray, self::TEST_FILE_TMP_XML, FileParser::CONTENT_TYPE_XML);
        FileParser::writeToFile($this->testArray, self::TEST_FILE_TMP_YAML, FileParser::CONTENT_TYPE_YAML);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_FILE_TMP_JSON);
        @unlink(self::TEST_FILE_TMP_ARRAY);
        @unlink(self::TEST_FILE_TMP_INI);
        @unlink(self::TEST_FILE_TMP_XML);
        @unlink(self::TEST_FILE_TMP_YAML);
    }

    /**
     * Data provider for parse tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        $jsonEntries  = new FileHasValidEntries(self::TEST_FILE_TMP_JSON, FileParser::CONTENT_TYPE_JSON);
        $arrayEntries = new FileHasValidEntries(self::TEST_FILE_TMP_ARRAY, FileParser::CONTENT_TYPE_ARRAY);
        $iniEntries   = new FileHasValidEntries(self::TEST_FILE_TMP_INI, FileParser::CONTENT_TYPE_INI);
        $xmlEntries   = new FileHasValidEntries(self::TEST_FILE_TMP_XML, FileParser::CONTENT_TYPE_XML);
        $yamlEntries  = new FileHasValidEntries(self::TEST_FILE_TMP_YAML, FileParser::CONTENT_TYPE_YAML);

        return [
            // FileHasValidEntries instance | expected result of search actions | expect an exception
            /** JSON TESTS */
            [(clone $jsonEntries)->hasKey('key1'), true, false],
            [(clone $jsonEntries)->hasKeyWithValue('key1', 'value1'), true, false],
            [(clone $jsonEntries)->hasKeyWithNonEmptyValue('key1'), true, false],
            [(clone $jsonEntries)->hasKeyWithEmptyValue('key99'), true, false],
            [(clone $jsonEntries)->hasKey('key2.key3'), true, false],
            [(clone $jsonEntries)->hasKey('key2.key4.key5'), true, false],
            /** Negative cases */
            [(clone $jsonEntries)->hasKeyWithValue('key1', 'value2'), false, true],
            [(clone $jsonEntries)->hasKeyWithEmptyValue('key1'), false, true],
            [(clone $jsonEntries)->hasKey('key2.key4.key7'), false, true],
            [(clone $jsonEntries)->hasKey('key2.key4.key5.key6'), false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_JSON), false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_JSON, ''), false, true],
            /** ARRAY TESTS */
            [(clone $arrayEntries)->hasKey('key1'), true, false],
            [(clone $arrayEntries)->hasKey('key2.key3'), true, false],
            [(clone $arrayEntries)->hasKey('key2.key4.key5'), true, false],
            [(clone $arrayEntries)->hasKey('key2.key4.key7'), false, true],
            /** Negative cases */
            [(clone $arrayEntries)->hasKeyWithValue('key1', 'value2'), false, true],
            [(clone $arrayEntries)->hasKeyWithEmptyValue('key1'), false, true],
            [(clone $arrayEntries)->hasKey('key2.key4.key7'), false, true],
            [(clone $arrayEntries)->hasKey('key2.key4.key5.key6'), false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_ARRAY), false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_ARRAY, ''), false, true],
            /** INI TESTS */
            [(clone $iniEntries)->hasKey('key1'), true, false],
            [(clone $iniEntries)->hasKey('key2.key3'), true, false],
            [(clone $iniEntries)->hasKey('key2.key4.key5'), true, false],
            [(clone $iniEntries)->hasKey('key2.key4.key7'), false, true],
            /** Negative cases */
            [(clone $iniEntries)->hasKeyWithValue('key1', 'value2'), false, true],
            [(clone $iniEntries)->hasKeyWithEmptyValue('key1'), false, true],
            [(clone $iniEntries)->hasKey('key2.key4.key7'), false, true],
            [(clone $iniEntries)->hasKey('key2.key4.key5.key6'), false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_INI), false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_INI, ''), false, true],
            /** XML TESTS */
            [(clone $xmlEntries)->hasKey('key1'), true, false],
            [(clone $xmlEntries)->hasKey('key2.key3'), true, false],
            [(clone $xmlEntries)->hasKey('key2.key4.key5'), true, false],
            [(clone $xmlEntries)->hasKey('key2.key4.key7'), false, true],
            /** Negative cases */
            [(clone $xmlEntries)->hasKeyWithValue('key1', 'value2'), false, true],
            [(clone $xmlEntries)->hasKeyWithEmptyValue('key1'), false, true],
            [(clone $xmlEntries)->hasKey('key2.key4.key7'), false, true],
            [(clone $xmlEntries)->hasKey('key2.key4.key5.key6'), false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_XML), false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_XML, ''), false, true],
            /** YAML TESTS */
            [(clone $yamlEntries)->hasKey('key1'), true, false],
            [(clone $yamlEntries)->hasKey('key2.key3'), true, false],
            [(clone $yamlEntries)->hasKey('key2.key4.key5'), true, false],
            [(clone $yamlEntries)->hasKey('key2.key4.key7'), false, true],
            /** Negative cases */
            [(clone $yamlEntries)->hasKeyWithValue('key1', 'value2'), false, true],
            [(clone $yamlEntries)->hasKeyWithEmptyValue('key1'), false, true],
            [(clone $yamlEntries)->hasKey('key2.key4.key7'), false, true],
            [(clone $yamlEntries)->hasKey('key2.key4.key5.key6'), false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), false, true],
        ];
    }

    /**
     * Test the function FileHasValidEntries::run().
     *
     * @dataProvider filesProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(FileHasValidEntries $fileHasValidEntries, bool $expected, $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->run());
    }
}
