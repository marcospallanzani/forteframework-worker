<?php

namespace Tests\Unit\Actions\Checks\Files;

use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\FileHasValidEntries;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\FileParser;
use Tests\Unit\BaseTest;

/**
 * Class FileHasValidEntriesTest
 *
 * @package Tests\Unit\Actions\Checks\Files
 */
class FileHasValidEntriesTest extends BaseTest
{
    /**
     * Temporary files constants
     */
    const TEST_FILE_TMP_JSON  = __DIR__ . '/file-tests.json';
    const TEST_FILE_TMP_INI   = __DIR__ . '/file-tests.ini';
    const TEST_FILE_TMP_YAML  = __DIR__ . '/file-tests.yml';
    const TEST_FILE_TMP_XML   = __DIR__ . '/file-tests.xml';
    const TEST_FILE_TMP_ARRAY = __DIR__ . '/file-tests.php';
    const TEST_FILE_TMP_EMPTY = __DIR__ . '/file-tests-empty.json';

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
        file_put_contents(self::TEST_FILE_TMP_EMPTY, '');
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
        @unlink(self::TEST_FILE_TMP_EMPTY);
    }

    /**
     * Data provider for isValid() tests.
     *
     * @return array
     */
    public function isValidProvider(): array
    {
        return [
            // File path | content type | check | expected | exception
            [self::TEST_FILE_TMP_JSON, FileParser::CONTENT_TYPE_JSON, null, true, false],
            [self::TEST_FILE_TMP_ARRAY, FileParser::CONTENT_TYPE_ARRAY, null, true, false],
            [self::TEST_FILE_TMP_INI, FileParser::CONTENT_TYPE_INI, null, true, false],
            [self::TEST_FILE_TMP_XML, FileParser::CONTENT_TYPE_XML, null, true, false],
            [self::TEST_FILE_TMP_YAML, FileParser::CONTENT_TYPE_YAML, null, true, false],
            /** Negative cases */
            ['', FileParser::CONTENT_TYPE_YAML, null, false, true],
            ['', '', null, false, true],
            [self::TEST_FILE_TMP_YAML, '', null, false, true],
            [self::TEST_FILE_TMP_YAML, 'wrong-content-type', null, false, true],
            [self::TEST_FILE_TMP_JSON, FileParser::CONTENT_TYPE_JSON, '', false, true],
            [self::TEST_FILE_TMP_ARRAY, FileParser::CONTENT_TYPE_ARRAY, '', false, true],
            [self::TEST_FILE_TMP_INI, FileParser::CONTENT_TYPE_INI, '', false, true],
            [self::TEST_FILE_TMP_XML, FileParser::CONTENT_TYPE_XML, '', false, true],
            [self::TEST_FILE_TMP_YAML, FileParser::CONTENT_TYPE_YAML, '', false, true],
        ];
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
            [$jsonEntries, 'key1', false, false, true, false],
            [$jsonEntries, 'key2.key3', false, false, true, false],
            [$jsonEntries, 'key2.key4.key5', false, false, true, false],
            /** Negative cases */
            [$jsonEntries, 'key2.key4.key7', true, false, false, true],
            [$jsonEntries, 'key2.key4.key5.key6', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_JSON), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_JSON, ''), 'key1', true, false, false, true],
            /** ARRAY TESTS */
            [$arrayEntries, 'key1', false, false, true, false],
            [$arrayEntries, 'key2.key3', false, false, true, false],
            [$arrayEntries, 'key2.key4.key5', false, false, true, false],
            [$arrayEntries, 'key2.key4.key7', true, false, false, true],
            /** Negative cases */
            [$arrayEntries, 'key2.key4.key7', true, false, false, true],
            [$arrayEntries, 'key2.key4.key5.key6', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_ARRAY), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_ARRAY, ''), 'key1', true, false, false, true],
            /** INI TESTS */
            [$iniEntries, 'key1', false, false, true, false],
            [$iniEntries, 'key2.key3', false, false, true, false],
            [$iniEntries, 'key2.key4.key5', false, false, true, false],
            [$iniEntries, 'key2.key4.key7', true, false, false, true],
            /** Negative cases */
            [$iniEntries, 'key2.key4.key7', true, false, false, true],
            [$iniEntries, 'key2.key4.key5.key6', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_INI), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_INI, ''), 'key1', true, false, false, true],
            /** XML TESTS */
            [$xmlEntries, 'key1', false, false, true, false],
            [$xmlEntries, 'key2.key3', false, false, true, false],
            [$xmlEntries, 'key2.key4.key5', false, false, true, false],
            [$xmlEntries, 'key2.key4.key7', true, false, false, true],
            /** Negative cases */
            [$xmlEntries, 'key2.key4.key7', true, false, false, true],
            [$xmlEntries, 'key2.key4.key5.key6', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_XML), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_XML, ''), 'key1', true, false, false, true],
            /** YAML TESTS */
            [$yamlEntries, 'key1', false, false, true, false],
            [$yamlEntries, 'key2.key3', false, false, true, false],
            [$yamlEntries, 'key2.key4.key5', false, false, true, false],
            [$yamlEntries, 'key2.key4.key7', true, false, false, true],
            /** Negative cases */
            [$yamlEntries, 'key2.key4.key7', true, false, false, true],
            [$yamlEntries, 'key2.key4.key5.key6', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', true, false, false, true],
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
            [clone $jsonEntries, 'key2.key4.key5.key6', false, false, true, false],
            [clone $arrayEntries, 'key2.key4.key5.key6', false, false,  true, false],
            [clone $iniEntries, 'key2.key4.key5.key6', false, false,  true, false],
            [clone $xmlEntries, 'key2.key4.key5.key6', false, false,  true, false],
            [clone $yamlEntries, 'key2.key4.key5.key6', false, false,  true, false],
            /** Negative cases */
            [clone $jsonEntries, 'key2.key3', true, false, false, true],
            [clone $arrayEntries, 'key2.key3', true, false, false, true],
            [clone $iniEntries, 'key2.key3', true, false, false, true],
            [clone $xmlEntries, 'key2.key3', true, false, false, true],
            [clone $yamlEntries, 'key2.key3', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', true, false, false, true],
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
            [clone $jsonEntries, 'key99', false, false, true, false],
            [clone $arrayEntries, 'key99', false, false, true, false],
            [clone $iniEntries, 'key99', false, false, true, false],
            [clone $xmlEntries, 'key99', false, false, true, false],
            [clone $yamlEntries, 'key99', false, false, true, false],
            /** Negative cases */
            [clone $jsonEntries, 'key2.key3', true, false, false, true],
            [clone $arrayEntries, 'key2.key3', true, false, false, true],
            [clone $iniEntries, 'key2.key3', true, false, false, true],
            [clone $xmlEntries, 'key2.key3', true, false, false, true],
            [clone $yamlEntries, 'key2.key3', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', true, false, false, true],
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
            [clone $jsonEntries, 'key2.key3', false, false, true, false],
            [clone $arrayEntries, 'key2.key3', false, false, true, false],
            [clone $iniEntries, 'key2.key3', false, false, true, false],
            [clone $xmlEntries, 'key2.key3', false, false, true, false],
            [clone $yamlEntries, 'key2.key3', false, false, true, false],
            /** Negative cases */
            [clone $jsonEntries, 'key99', true, false, false, true],
            [clone $arrayEntries, 'key99', true, false, false, true],
            [clone $iniEntries, 'key99', true, false, false, true],
            [clone $xmlEntries, 'key99', true, false, false, true],
            [clone $yamlEntries, 'key99', true, false, false, true],
            [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', true, false, false, true],
            [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', true, false, false, true],
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
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key1', 'ue1', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key1', 'val', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key1', '1', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key3', 'ue3', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key3', 'val', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key3', '3', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'ue5', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'val', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                [clone $instance, 'key2.key4.key5', '5', VerifyArray::CHECK_CONTAINS, false, false, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'yrew', VerifyArray::CHECK_CONTAINS, true, false, false, true],
                [clone $instance, 'key2.key4.key5', '3', VerifyArray::CHECK_CONTAINS, true, false, false, true],
                [clone $instance, 'key2.key3', 'xxx', VerifyArray::CHECK_CONTAINS, true, false, false, true],
                [clone $instance, 'key99', 'xxx', VerifyArray::CHECK_CONTAINS, true, false, false, true],
                [new FileHasValidEntries('', FileParser::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_CONTAINS, true, false, false, true],
                [new FileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_CONTAINS, true, false, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_STARTS_WITH */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key1', 'val', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key1', 'v', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key2.key3', 'val', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key2.key3', 'v', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'val', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'v', VerifyArray::CHECK_STARTS_WITH, false, false, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'alue', VerifyArray::CHECK_STARTS_WITH, true, false, false, true],
                [clone $instance, 'key2.key4.key5', 'lue5', VerifyArray::CHECK_STARTS_WITH, true, false, false, true],
                [clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_STARTS_WITH, true, false, false, true],
                [clone $instance, 'key99', 'lue', VerifyArray::CHECK_STARTS_WITH, true, false, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_ENDS_WITH */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key1', 'ue1', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key1', '1', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key2.key3', 'ue3', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key2.key3', '3', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'ue5', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                [clone $instance, 'key2.key4.key5', '5', VerifyArray::CHECK_ENDS_WITH, false, false, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'alue', VerifyArray::CHECK_ENDS_WITH, true, false, false, true],
                [clone $instance, 'key2.key4.key5', 'lue', VerifyArray::CHECK_ENDS_WITH, true, false, false, true],
                [clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_ENDS_WITH, true, false, false, true],
                [clone $instance, 'key99', 'lue', VerifyArray::CHECK_ENDS_WITH, true, false, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_EQUALS */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EQUALS, false, false, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EQUALS, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EQUALS, false, false, true, false],
                [clone $instance, 'key99', '', VerifyArray::CHECK_EQUALS, false, false, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'alue', VerifyArray::CHECK_EQUALS, true, false, false, true],
                [clone $instance, 'key2.key4.key5', 'lue', VerifyArray::CHECK_EQUALS, true, false, false, true],
                [clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_EQUALS, true, false, false, true],
                [clone $instance, 'key99', 'lue', VerifyArray::CHECK_EQUALS, true, false, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_EMPTY */
                [clone $instance, 'key99', '', VerifyArray::CHECK_EMPTY, false, false, true, false],
                /** Negative cases */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EMPTY, true, false, false, true],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EMPTY, true, false, false, true],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EMPTY, true, false, false, true],
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_ANY */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_ANY, false, false, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ANY, false, false, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_ANY, false, false, true, false],
                [clone $instance, 'key99', '', VerifyArray::CHECK_ANY, false, false, true, false],
                /** Negative cases */
            ]);

            $providedValues = array_merge($providedValues, [
                /** CHECK_MISSING_KEY */
                [clone $instance, 'key1000', 'value1', VerifyArray::CHECK_MISSING_KEY, false, false, true, false],
                /** Negative cases */
            ]);
        }

        return $providedValues;
    }

    /**
     * Data provider for stringify tests.
     *
     * @return array
     */
    public function stringifyProvider(): array
    {
        $filePath = self::TEST_FILE_TMP_JSON;
        $key = 'key';
        $value = "value";
        return [
            [(new FileHasValidEntries($filePath, FileParser::CONTENT_TYPE_JSON)), "Run the following checks in file '$filePath':"],
            [(new FileHasValidEntries($filePath, FileParser::CONTENT_TYPE_JSON))->hasKey($key), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and has any value"],
            [(new FileHasValidEntries($filePath, FileParser::CONTENT_TYPE_JSON))->hasKeyWithNonEmptyValue($key), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and is not empty (empty string or null)"],
            [(new FileHasValidEntries($filePath, FileParser::CONTENT_TYPE_JSON))->hasKeyWithEmptyValue($key), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and is empty (empty string or null)"],
            [(new FileHasValidEntries($filePath, FileParser::CONTENT_TYPE_JSON))->hasKeyWithValue($key, $value, VerifyArray::CHECK_EQUALS), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and is equal to value '$value'"],
            [(new FileHasValidEntries($filePath, FileParser::CONTENT_TYPE_JSON))->hasKeyWithValue($key, $value, VerifyArray::CHECK_CONTAINS), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and contains value '$value'"],
        ];
    }

    /**
     * Test the function FileHasValidEntries::run() with the HasKey check.
     *
     * @dataProvider filesHasKeyProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKey(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $isFatal,
        bool $isSuccessRequired,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries->hasKey($key)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function FileHasValidEntries::isValid().
     *
     * @dataProvider isValidProvider
     *
     * @param string $filePath
     * @param string $contentType
     * @param mixed $verifyKey
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testIsValid(
        string $filePath,
        string $contentType,
        $verifyKey,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        $fileHasValidEntries = new FileHasValidEntries($filePath, $contentType);
        if (is_string($verifyKey)) {
            $fileHasValidEntries->hasKey($verifyKey);
        }
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->isValid());
    }

    /**
     * If we try to parse an empty json file (created with wrong syntax use), we should get a RuntimeException.
     */
    public function testRunHasKeyWithEmptyFile(): void
    {
        // An exception should be thrown here because the file is empty and it is decoded to an empty string
        $this->expectException(WorkerException::class);
        $fileHasValidEntries = new FileHasValidEntries(self::TEST_FILE_TMP_EMPTY);
        $fileHasValidEntries->setIsFatal(true);
        $fileHasValidEntries->contentType(FileParser::CONTENT_TYPE_JSON)->hasKey('test1')->run();
    }

    /**
     * Test the function FileHasValidEntries::run() with the DoesNotHasKey check.
     *
     * @dataProvider filesDoesNotHaveKeyProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunDoesNotHaveKey(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $isFatal,
        bool $isSuccessRequired,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries->doesNotHaveKey($key)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function FileHasValidEntries::run() with the KeyWithEmptyValue check.
     *
     * @dataProvider filesHasKeyWithEmptyValueProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithEmptyValue(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $isFatal,
        bool $isSuccessRequired,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries->hasKeyWithEmptyValue($key)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function FileHasValidEntries::run() with the KeyWithNonEmptyValue check.
     *
     * @dataProvider filesHasKeyWithNonEmptyValueProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithNonEmptyValue(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        bool $isFatal,
        bool $isSuccessRequired,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries->hasKeyWithNonEmptyValue($key)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
                ->run()
                ->getResult()
        );
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
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithValue(
        FileHasValidEntries $fileHasValidEntries,
        string $key,
        $value,
        string $compareActionType,
        bool $isFatal,
        bool $isSuccessRequired,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries->hasKeyWithValue($key, $value, $compareActionType)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test method FileHasValidEntries::stringify().
     *
     * @dataProvider stringifyProvider
     *
     * @param FileHasValidEntries $fileHasValidEntries
     * @param string $expected
     */
    public function testStringify(FileHasValidEntries $fileHasValidEntries, string $expected): void
    {
        $this->assertEquals($expected, (string) $fileHasValidEntries);
        $this->assertEquals($expected, $fileHasValidEntries->stringify());
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
