<?php

namespace Tests\Unit\Actions\Checks\Files;

use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
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
     * Data provider for file-has-key tests.
     *
     * @return array
     */
    public function filesHasKeyProvider(): array
    {
        list($jsonEntries, $arrayEntries, $iniEntries, $xmlEntries, $yamlEntries) = $this->getFileHasValidEntriesInstances();

        return [
            // FileHasValidEntries instance | expected result of search actions | expect an exception
            /** JSON TESTS */
            [$jsonEntries, 'key1', true, false],
            [$jsonEntries, 'key2.key3', true, false],
            [$jsonEntries, 'key2.key4.key5', true, false],
            /** Negative cases */
            [$jsonEntries, 'key2.key4.key7', false, true],
            [$jsonEntries, 'key2.key4.key5.key6', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_JSON), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_JSON, ''), 'key1', false, true],
            /** ARRAY TESTS */
            [$arrayEntries, 'key1', true, false],
            [$arrayEntries, 'key2.key3', true, false],
            [$arrayEntries, 'key2.key4.key5', true, false],
            [$arrayEntries, 'key2.key4.key7', false, true],
            /** Negative cases */
            [$arrayEntries, 'key2.key4.key7', false, true],
            [$arrayEntries, 'key2.key4.key5.key6', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_ARRAY), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_ARRAY, ''), 'key1', false, true],
            /** INI TESTS */
            [$iniEntries, 'key1', true, false],
            [$iniEntries, 'key2.key3', true, false],
            [$iniEntries, 'key2.key4.key5', true, false],
            [$iniEntries, 'key2.key4.key7', false, true],
            /** Negative cases */
            [$iniEntries, 'key2.key4.key7', false, true],
            [$iniEntries, 'key2.key4.key5.key6', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_INI), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_INI, ''), 'key1', false, true],
            /** XML TESTS */
            [$xmlEntries, 'key1', true, false],
            [$xmlEntries, 'key2.key3', true, false],
            [$xmlEntries, 'key2.key4.key5', true, false],
            [$xmlEntries, 'key2.key4.key7', false, true],
            /** Negative cases */
            [$xmlEntries, 'key2.key4.key7', false, true],
            [$xmlEntries, 'key2.key4.key5.key6', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_XML), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_XML, ''), 'key1', false, true],
            /** YAML TESTS */
            [$yamlEntries, 'key1', true, false],
            [$yamlEntries, 'key2.key3', true, false],
            [$yamlEntries, 'key2.key4.key5', true, false],
            [$yamlEntries, 'key2.key4.key7', false, true],
            /** Negative cases */
            [$yamlEntries, 'key2.key4.key7', false, true],
            [$yamlEntries, 'key2.key4.key5.key6', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', false, true],
        ];
    }

    /**
     * Data provider for file-does-not-have-key tests.
     *
     * @return array
     */
    public function filesDoesNotHaveKeyProvider(): array
    {
        list($jsonEntries, $arrayEntries, $iniEntries, $xmlEntries, $yamlEntries) = $this->getFileHasValidEntriesInstances();

        return [
            [clone $jsonEntries, 'key2.key4.key5.key6', true, false],
            [clone $arrayEntries, 'key2.key4.key5.key6', true, false],
            [clone $iniEntries, 'key2.key4.key5.key6', true, false],
            [clone $xmlEntries, 'key2.key4.key5.key6', true, false],
            [clone $yamlEntries, 'key2.key4.key5.key6', true, false],
            /** Negative cases */
            [clone $jsonEntries, 'key2.key3', false, true],
            [clone $arrayEntries, 'key2.key3', false, true],
            [clone $iniEntries, 'key2.key3', false, true],
            [clone $xmlEntries, 'key2.key3', false, true],
            [clone $yamlEntries, 'key2.key3', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', false, true],
        ];
    }

    /**
     * Data provider for file-has-key-with-empty-value tests.
     *
     * @return array
     */
    public function filesHasKeyWithEmptyValueProvider(): array
    {
        list($jsonEntries, $arrayEntries, $iniEntries, $xmlEntries, $yamlEntries) = $this->getFileHasValidEntriesInstances();

        return [
            [clone $jsonEntries, 'key99', true, false],
            [clone $arrayEntries, 'key99', true, false],
            [clone $iniEntries, 'key99', true, false],
            [clone $xmlEntries, 'key99', true, false],
            [clone $yamlEntries, 'key99', true, false],
            /** Negative cases */
            [clone $jsonEntries, 'key2.key3', false, true],
            [clone $arrayEntries, 'key2.key3', false, true],
            [clone $iniEntries, 'key2.key3', false, true],
            [clone $xmlEntries, 'key2.key3', false, true],
            [clone $yamlEntries, 'key2.key3', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', false, true],
        ];
    }

    /**
     * Data provider for file-has-key-with-non-empty-value tests.
     *
     * @return array
     */
    public function filesHasKeyWithNonEmptyValueProvider(): array
    {
        list($jsonEntries, $arrayEntries, $iniEntries, $xmlEntries, $yamlEntries) = $this->getFileHasValidEntriesInstances();

        return [
            [clone $jsonEntries, 'key2.key3', true, false],
            [clone $arrayEntries, 'key2.key3', true, false],
            [clone $iniEntries, 'key2.key3', true, false],
            [clone $xmlEntries, 'key2.key3', true, false],
            [clone $yamlEntries, 'key2.key3', true, false],
            /** Negative cases */
            [clone $jsonEntries, 'key99', false, true],
            [clone $arrayEntries, 'key99', false, true],
            [clone $iniEntries, 'key99', false, true],
            [clone $xmlEntries, 'key99', false, true],
            [clone $yamlEntries, 'key99', false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', false, true],
        ];
    }

    /**
     * Data provider for file-has-key-with-value tests.
     *
     * @return array
     */
    public function filesHasKeyWithValueProvider(): array
    {
        $providedValues = [];

        $fileEntriesInstances = $this->getFileHasValidEntriesInstances();
        foreach ($fileEntriesInstances as $instance) {
            $providedValues = array_merge($providedValues, [
                /** CHECK_CONTAINS */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key1', 'ue1', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key1', 'val', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key1', '1', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key3', 'ue3', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key3', 'val', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key3', '3', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key4.key5', 'ue5', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key4.key5', 'val', VerifyArray::CHECK_CONTAINS, true, false],
                [clone $instance, 'key2.key4.key5', '5', VerifyArray::CHECK_CONTAINS, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'yrew', VerifyArray::CHECK_CONTAINS, false, true],
                [clone $instance, 'key2.key4.key5', '3', VerifyArray::CHECK_CONTAINS, false, true],
                [clone $instance, 'key2.key3', 'xxx', VerifyArray::CHECK_CONTAINS, false, true],
                [clone $instance, 'key99', 'xxx', VerifyArray::CHECK_CONTAINS, false, true],
                [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_CONTAINS, false, true],
                [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_CONTAINS, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_STARTS_WITH */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key1', 'val', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key1', 'v', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key2.key3', 'val', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key2.key3', 'v', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key2.key4.key5', 'val', VerifyArray::CHECK_STARTS_WITH, true, false],
                [clone $instance, 'key2.key4.key5', 'v', VerifyArray::CHECK_STARTS_WITH, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'alue', VerifyArray::CHECK_STARTS_WITH, false, true],
                [clone $instance, 'key2.key4.key5', 'lue5', VerifyArray::CHECK_STARTS_WITH, false, true],
                [clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_STARTS_WITH, false, true],
                [clone $instance, 'key99', 'lue', VerifyArray::CHECK_STARTS_WITH, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_ENDS_WITH */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key1', 'ue1', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key1', '1', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key2.key3', 'ue3', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key2.key3', '3', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key2.key4.key5', 'ue5', VerifyArray::CHECK_ENDS_WITH, true, false],
                [clone $instance, 'key2.key4.key5', '5', VerifyArray::CHECK_ENDS_WITH, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'alue', VerifyArray::CHECK_ENDS_WITH, false, true],
                [clone $instance, 'key2.key4.key5', 'lue', VerifyArray::CHECK_ENDS_WITH, false, true],
                [clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_ENDS_WITH, false, true],
                [clone $instance, 'key99', 'lue', VerifyArray::CHECK_ENDS_WITH, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_EQUALS */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EQUALS, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EQUALS, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EQUALS, true, false],
                [clone $instance, 'key99', '', VerifyArray::CHECK_EQUALS, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'alue', VerifyArray::CHECK_EQUALS, false, true],
                [clone $instance, 'key2.key4.key5', 'lue', VerifyArray::CHECK_EQUALS, false, true],
                [clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_EQUALS, false, true],
                [clone $instance, 'key99', 'lue', VerifyArray::CHECK_EQUALS, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_EMPTY */
                [clone $instance, 'key99', '', VerifyArray::CHECK_EMPTY, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EMPTY, false, true],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EMPTY, false, true],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EMPTY, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_ANY */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_ANY, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ANY, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_ANY, true, false],
                [clone $instance, 'key99', '', VerifyArray::CHECK_ANY, true, false],
                /** Negative cases */
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_MISSING_KEY */
                [clone $instance, 'key1000', 'value1', VerifyArray::CHECK_MISSING_KEY, true, false],
                /** Negative cases */
            ]);
        }

        return $providedValues;
    }




    /**
     * Test the function FileHasValidEntries::run() with the HasKey check.
     *
     * @dataProvider filesHasKeyProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKey(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->hasKey($key)->run());
    }

    /**
     * Test the function FileHasValidEntries::run() with the DoesNotHasKey check.
     *
     * @dataProvider filesDoesNotHaveKeyProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunDoesNotHaveKey(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->doesNotHaveKey($key)->run());
    }

    /**
     * Test the function FileHasValidEntries::run() with the KeyWithEmptyValue check.
     *
     * @dataProvider filesHasKeyWithEmptyValueProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithEmptyValue(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->hasKeyWithEmptyValue($key)->run());
    }

    /**
     * Test the function FileHasValidEntries::run() with the KeyWithNonEmptyValue check.
     *
     * @dataProvider filesHasKeyWithNonEmptyValueProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithNonEmptyValue(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->hasKeyWithNonEmptyValue($key)->run());
    }

    /**
     * Test the function FileHasValidEntries::run() with the KeyWithValue check.
     *
     * @dataProvider filesHasKeyWithValueProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param mixed $value
     * @param string $compareActionType
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithValue(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        $value,
        string $compareActionType,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->hasKeyWithValue($key, $value, $compareActionType)->run());
    }

    /**
     * @return array
     */
    protected function getFileHasValidEntriesInstances(): array
    {
        return [
            new FileHasValidEntries(self::TEST_FILE_TMP_JSON, FileParser::CONTENT_TYPE_JSON),
            new FileHasValidEntries(self::TEST_FILE_TMP_ARRAY, FileParser::CONTENT_TYPE_ARRAY),
            new FileHasValidEntries(self::TEST_FILE_TMP_INI, FileParser::CONTENT_TYPE_INI),
            new FileHasValidEntries(self::TEST_FILE_TMP_XML, FileParser::CONTENT_TYPE_XML),
            new FileHasValidEntries(self::TEST_FILE_TMP_YAML, FileParser::CONTENT_TYPE_YAML),
        ];
    }
}
