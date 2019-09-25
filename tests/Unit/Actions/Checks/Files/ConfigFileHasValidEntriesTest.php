<?php

namespace Forte\Worker\Tests\Unit\Actions\Checks\Files;

use Forte\Stdlib\Exceptions\GeneralException;
use Forte\Stdlib\FileUtils;
use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\ConfigFileHasValidEntries;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ConfigFileHasValidEntriesTest
 *
 * @package Forte\Worker\Tests\Unit\Actions\Checks\Files
 */
class ConfigFileHasValidEntriesTest extends BaseTest
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
     * @throws GeneralException
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

        FileUtils::writeToFile($this->testArray, self::TEST_FILE_TMP_JSON, FileUtils::CONTENT_TYPE_JSON);
        FileUtils::writeToFile($this->testArray, self::TEST_FILE_TMP_ARRAY, FileUtils::CONTENT_TYPE_ARRAY);
        FileUtils::writeToFile($this->testArray, self::TEST_FILE_TMP_INI, FileUtils::CONTENT_TYPE_INI);
        FileUtils::writeToFile($this->testArray, self::TEST_FILE_TMP_XML, FileUtils::CONTENT_TYPE_XML);
        FileUtils::writeToFile($this->testArray, self::TEST_FILE_TMP_YAML, FileUtils::CONTENT_TYPE_YAML);
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
            [self::TEST_FILE_TMP_JSON, FileUtils::CONTENT_TYPE_JSON, null, true, false],
            [self::TEST_FILE_TMP_ARRAY, FileUtils::CONTENT_TYPE_ARRAY, null, true, false],
            [self::TEST_FILE_TMP_INI, FileUtils::CONTENT_TYPE_INI, null, true, false],
            [self::TEST_FILE_TMP_XML, FileUtils::CONTENT_TYPE_XML, null, true, false],
            [self::TEST_FILE_TMP_YAML, FileUtils::CONTENT_TYPE_YAML, null, true, false],
            /** Negative cases */
            ['', FileUtils::CONTENT_TYPE_YAML, null, false, true],
            ['', '', null, false, true],
            [self::TEST_FILE_TMP_YAML, '', null, false, true],
            [self::TEST_FILE_TMP_YAML, 'wrong-content-type', null, false, true],
            [self::TEST_FILE_TMP_JSON, FileUtils::CONTENT_TYPE_JSON, '', false, true],
            [self::TEST_FILE_TMP_ARRAY, FileUtils::CONTENT_TYPE_ARRAY, '', false, true],
            [self::TEST_FILE_TMP_INI, FileUtils::CONTENT_TYPE_INI, '', false, true],
            [self::TEST_FILE_TMP_XML, FileUtils::CONTENT_TYPE_XML, '', false, true],
            [self::TEST_FILE_TMP_YAML, FileUtils::CONTENT_TYPE_YAML, '', false, true],
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
//TODO ADD CASES FOR IS SUCCESS REQUIRED
        return [
            // ConfigFileHasValidEntries instance | key | severity | expected | expect an exception
            /** JSON TESTS */
            [$jsonEntries, 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$jsonEntries, 'key2.key3', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$jsonEntries, 'key2.key4.key5', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Negative cases */
            /** not successful, no fatal */
            [$jsonEntries, 'key2.key4.key7', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$jsonEntries, 'key2.key4.key5.key6', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_JSON), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_JSON, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            // The only way to throw an action exception is to break one or more validation checks
            [$jsonEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_JSON), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_JSON, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** ARRAY TESTS */
            [$arrayEntries, 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$arrayEntries, 'key2.key3', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$arrayEntries, 'key2.key4.key5', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Negative cases */
            /** not successful, no fatal */
            [$arrayEntries, 'key2.key4.key7', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$arrayEntries, 'key2.key4.key5.key6', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_ARRAY), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_ARRAY, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            // The only way to throw an action exception is to break one or more validation checks
            [$arrayEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_ARRAY), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_ARRAY, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** INI TESTS */
            [$iniEntries, 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$iniEntries, 'key2.key3', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$iniEntries, 'key2.key4.key5', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Negative cases */
            /** not successful, no fatal */
            [$iniEntries, 'key2.key4.key7', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$iniEntries, 'key2.key4.key5.key6', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_INI), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_INI, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            // The only way to throw an action exception is to break one or more validation checks
            [$iniEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_INI), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_INI, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** XML TESTS */
            [$xmlEntries, 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$xmlEntries, 'key2.key3', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$xmlEntries, 'key2.key4.key5', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Negative cases */
            /** not successful, no fatal */
            [$xmlEntries, 'key2.key4.key7', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$xmlEntries, 'key2.key4.key5.key6', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_XML), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_INI, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            // The only way to throw an action exception is to break one or more validation checks
            [$xmlEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_XML), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_XML, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],

            /** YAML TESTS */
            [$yamlEntries, 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$yamlEntries, 'key2.key3', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [$yamlEntries, 'key2.key4.key5', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Negative cases */
            /** not successful, no fatal */
            [$yamlEntries, 'key2.key4.key7', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [$yamlEntries, 'key2.key4.key5.key6', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            // The only way to throw an action exception is to break one or more validation checks
            [$yamlEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
        ];
    }

    /**
     * Data provider for file-has-xxx tests.
     *
     * @param string $testName
     * @param string $validKey
     * @param string $failKey
     *
     * @return array
     */
    public function fileHasSomethingProvider(string $testName, string $validKey, string $failKey): array
    {
        list($jsonEntries, $arrayEntries, $iniEntries, $xmlEntries, $yamlEntries) = $this->getFileHasValidEntriesInstances();

        return [
            [clone $jsonEntries, $validKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [clone $arrayEntries, $validKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [clone $iniEntries, $validKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [clone $xmlEntries, $validKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [clone $yamlEntries, $validKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            /** Negative cases */
            /** not successful, no fatal */
            [clone $jsonEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [clone $arrayEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [clone $iniEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [clone $xmlEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [clone $yamlEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            /** not successful, fatal */
            [clone $jsonEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [clone $arrayEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [clone $iniEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [clone $xmlEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [clone $yamlEntries, '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), '', ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            /** successful with negative result, critical */
//TODO IMPLEMENT IS SUCCESS REQUIRED CASE: THE NEGATIVE RESULT OF AN ACTION SETS THE MAIN ACTION RESULT TO ITS NEGATIVE CASE (CHILD ACTION, PRE-RUN ETC)
            /** successful with negative result, critical */
            [clone $jsonEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            [clone $arrayEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            [clone $iniEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            [clone $xmlEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            [clone $yamlEntries, $failKey, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), '', ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            [ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), '', ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
        ];

    }

    /**
     * Data provider for file-does-not-have-key tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function filesDoesNotHaveKeyProvider(string $testName): array
    {
        return $this->fileHasSomethingProvider($testName, 'key2.key4.key5.key6', 'key2.key3');
    }

    /**
     * Data provider for file-has-key-with-empty-value tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function filesHasKeyWithEmptyValueProvider(string $testName): array
    {
        return $this->fileHasSomethingProvider($testName, 'key99', 'key2.key3');
    }

    /**
     * Data provider for file-has-key-with-non-empty-value tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function filesHasKeyWithNonEmptyValueProvider(string $testName): array
    {
        return $this->fileHasSomethingProvider($testName, 'key2.key3', 'key99');
    }

    /**
     * Data provider for file-has-key-with-value tests.
     *
     * @return array
     */
    public function filesHasKeyWithValueProvider(): array
    {
        $providedValues = [];

        $failParams = [ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false];
        $fatalParams = [ActionInterface::EXECUTION_SEVERITY_FATAL, false, true];
        $criticalParams = [ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true];

        $fileEntriesInstances = $this->getFileHasValidEntriesInstances();
        foreach ($fileEntriesInstances as $instance) {
            // Instance | key | value | compare action | severity | expected | exception | case sensitive
            $providedValues = array_merge($providedValues, [
                /** CHECK_CONTAINS */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', 'ue1', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', 'val', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', '1', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'ue3', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'val', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', '3', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'ue5', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'val', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', '5', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Case sensitive tests */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
                [clone $instance, 'key1', 'VALUE1', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, false],
                [clone $instance, 'key1', 'Val', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                [clone $instance, 'key1', 'test', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                [clone $instance, 'key1', 'test', VerifyArray::CHECK_CONTAINS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, false],
                /** Negative cases */
                /** not successful, no fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, 'key1', 'yrew', VerifyArray::CHECK_CONTAINS], $failParams),
                array_merge([clone $instance, 'key2.key4.key5', '', VerifyArray::CHECK_CONTAINS], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_CONTAINS], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_CONTAINS], $failParams),
                /** not successful, fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, '', 'yrew', VerifyArray::CHECK_CONTAINS], $fatalParams),
                array_merge([clone $instance, '', '', VerifyArray::CHECK_CONTAINS], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_CONTAINS], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_CONTAINS], $fatalParams),
                /** successful with negative result, is success required */
                array_merge([clone $instance, 'key1', 'yrew', VerifyArray::CHECK_CONTAINS], $criticalParams),
                array_merge([clone $instance, 'key2.key4.key5', '3', VerifyArray::CHECK_CONTAINS], $criticalParams),
                array_merge([clone $instance, 'key2.key3', 'xxx', VerifyArray::CHECK_CONTAINS], $criticalParams),
                array_merge([clone $instance, 'key99', 'xxx', VerifyArray::CHECK_CONTAINS], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_CONTAINS], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_CONTAINS], $criticalParams),

            ]);

            // Instance | key | value | compare action | severity| expected | exception
            $providedValues = array_merge($providedValues, [
                /** CHECK_STARTS_WITH */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', 'val', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', 'v', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'val', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'v', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'val', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'v', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Case sensitive tests */
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
                [clone $instance, 'key2.key3', 'VALUE', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'VALUE', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                [clone $instance, 'key2.key3', 'LUE', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
                [clone $instance, 'key2.key3', 'LUE', VerifyArray::CHECK_STARTS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                /** Negative cases */
                /** not successful, no fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, 'key1', 'alue', VerifyArray::CHECK_STARTS_WITH], $failParams),
                array_merge([clone $instance, 'key2.key4.key5', '', VerifyArray::CHECK_STARTS_WITH], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH], $failParams),
                /** not successful, fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, '', 'alue', VerifyArray::CHECK_STARTS_WITH], $fatalParams),
                array_merge([clone $instance, '', '', VerifyArray::CHECK_STARTS_WITH], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH], $fatalParams),
                /** successful with negative result, is success required */
                array_merge([clone $instance, 'key1', 'alue', VerifyArray::CHECK_STARTS_WITH], $criticalParams),
                array_merge([clone $instance, 'key2.key4.key5', 'lue5', VerifyArray::CHECK_STARTS_WITH], $criticalParams),
                array_merge([clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_STARTS_WITH], $criticalParams),
                array_merge([clone $instance, 'key99', 'lue', VerifyArray::CHECK_STARTS_WITH], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_STARTS_WITH], $criticalParams),
            ]);

            // Instance | key | value | compare action | severity | expected | exception
            $providedValues = array_merge($providedValues, [
                /** CHECK_ENDS_WITH */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', 'ue1', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key1', '1', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'ue3', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', '3', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'ue5', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', '5', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Case sensitive tests */
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
                [clone $instance, 'key2.key3', 'LUE3', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'LUE3', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                [clone $instance, 'key2.key3', 'xxx', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, false],
                [clone $instance, 'key2.key3', 'xxx', VerifyArray::CHECK_ENDS_WITH, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                /** Negative cases */
                /** not successful, no fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, 'key1', 'alue', VerifyArray::CHECK_ENDS_WITH], $failParams),
                array_merge([clone $instance, 'key2.key4.key5', '', VerifyArray::CHECK_ENDS_WITH], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH], $failParams),
                /** not successful, fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, '', 'alue', VerifyArray::CHECK_ENDS_WITH], $fatalParams),
                array_merge([clone $instance, '', '', VerifyArray::CHECK_ENDS_WITH], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH], $fatalParams),
                /** successful with negative result, is success required */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, 'key1', 'alue', VerifyArray::CHECK_ENDS_WITH], $criticalParams),
                array_merge([clone $instance, 'key2.key4.key5', '', VerifyArray::CHECK_ENDS_WITH], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_ENDS_WITH], $criticalParams),
            ]);

            // Instance | key | value | compare action | is fatal | is success required | expected | exception
            $providedValues = array_merge($providedValues, [
                /** CHECK_EQUALS */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key99', '', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Case sensitive tests */
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, true],
                [clone $instance, 'key2.key4.key5', 'VALUE5', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'VALUE5', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                [clone $instance, 'key2.key4.key5', 'test', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
                [clone $instance, 'key2.key4.key5', 'test', VerifyArray::CHECK_EQUALS, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, true],
                /** Negative cases */
                /** not successful, no fatal */
                array_merge([clone $instance, 'key1', 'alue', VerifyArray::CHECK_EQUALS], $failParams),
                array_merge([clone $instance, 'key2.key4.key5', 'lue', VerifyArray::CHECK_EQUALS], $failParams),
                array_merge([clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_EQUALS], $failParams),
                array_merge([clone $instance, 'key99', 'lue', VerifyArray::CHECK_EQUALS], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_EQUALS], $failParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_EQUALS], $failParams),
                /** not successful, fatal */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, '', 'alue', VerifyArray::CHECK_EQUALS], $fatalParams),
                array_merge([clone $instance, '', '', VerifyArray::CHECK_EQUALS], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_EQUALS], $fatalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_EQUALS], $fatalParams),
                /** successful with negative result, is success required */
                // The only way to throw an action exception is to break one or more validation checks
                array_merge([clone $instance, 'key1', 'alue', VerifyArray::CHECK_EQUALS], $criticalParams),
                array_merge([clone $instance, 'key2.key4.key5', 'lue', VerifyArray::CHECK_EQUALS], $criticalParams),
                array_merge([clone $instance, 'key2.key3', 'lue', VerifyArray::CHECK_EQUALS], $criticalParams),
                array_merge([clone $instance, 'key99', 'lue', VerifyArray::CHECK_EQUALS], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries('', FileUtils::CONTENT_TYPE_YAML), 'key1', 'value1', VerifyArray::CHECK_EQUALS], $criticalParams),
                array_merge([ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, ''), 'key1', 'value1', VerifyArray::CHECK_EQUALS], $criticalParams),
            ]);

            // Instance | key | value | compare action | severity | expected | exception
            $providedValues = array_merge($providedValues, [
//TODO CHANGE THE IS FATAL CASES WHEN THE ISVALID WILL BEHAVE AS THE RUN METHOD
                /** CHECK_EMPTY */
                [clone $instance, 'key99', '', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Negative cases */
                /** is fatal case: action successfully run with no errors, then no exception */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_FATAL, false, false],
                /** is success case: action successfully but with negative result, then exception */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
                /** is fatal case: action successfully run with no errors, then no exception */
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_FATAL, false, false],
                /** is success case: action successfully but with negative result, then exception */
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
                /** is fatal case: action successfully run with no errors, then no exception */
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_FATAL, false, false],
                /** is success case: action successfully but with negative result, then exception */
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_EMPTY, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true],
            ]);

            // Instance | key | value | compare action | severity | expected | exception
            $providedValues = array_merge($providedValues, [
                /** CHECK_ANY */
                [clone $instance, 'key1', 'value1', VerifyArray::CHECK_ANY, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key3', 'value3', VerifyArray::CHECK_ANY, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key2.key4.key5', 'value5', VerifyArray::CHECK_ANY, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                [clone $instance, 'key99', '', VerifyArray::CHECK_ANY, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Negative cases */
            ]);

            // Instance | key | value | compare action | is fatal | is success required | expected | exception
            $providedValues = array_merge($providedValues, [
                /** CHECK_MISSING_KEY */
                [clone $instance, 'key1000', 'value1', VerifyArray::CHECK_MISSING_KEY, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
                /** Negative cases */
            ]);
        }

        return $providedValues;
    }

    /**
     * Data provider for stringify tests.
     *
     * @param string $testName
     * @param bool $caseSensitive
     *
     * @return array
     */
    public function stringifyProvider(string $testName, bool $caseSensitive = false): array
    {
        if ($caseSensitive) {
            $caseSensitiveMessage = "(case sensitive)";
        } else {
            $caseSensitiveMessage = "(case insensitive)";
        }
        $filePath = self::TEST_FILE_TMP_JSON;
        $key = 'key';
        $value = "value";

        return [
            [ActionFactory::createConfigFileHasValidEntries($filePath, FileUtils::CONTENT_TYPE_JSON), "Run the following checks in file '$filePath':"],
            [ActionFactory::createConfigFileHasValidEntries($filePath, FileUtils::CONTENT_TYPE_JSON)->hasKey($key), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and has any value"],
            [ActionFactory::createConfigFileHasValidEntries($filePath, FileUtils::CONTENT_TYPE_JSON)->hasKeyWithNonEmptyValue($key), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and is not empty (empty string or null)"],
            [ActionFactory::createConfigFileHasValidEntries($filePath, FileUtils::CONTENT_TYPE_JSON)->hasKeyWithEmptyValue($key), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and is empty (empty string or null)"],
            [ActionFactory::createConfigFileHasValidEntries($filePath, FileUtils::CONTENT_TYPE_JSON)->hasKeyWithValue($key, $value, VerifyArray::CHECK_EQUALS, $caseSensitive), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and is equal to value '$value' $caseSensitiveMessage"],
            [ActionFactory::createConfigFileHasValidEntries($filePath, FileUtils::CONTENT_TYPE_JSON)->hasKeyWithValue($key, $value, VerifyArray::CHECK_CONTAINS, $caseSensitive), "Run the following checks in file '$filePath': 0. Check if key '$key' is set and contains value '$value' $caseSensitiveMessage"],
        ];
    }

    /**
     * Data provider for case-insensitive stringify tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function stringifyCaseInsensitiveProvider(string $testName): array
    {
        return $this->stringifyProvider($testName, true);
    }

    /**
     * Test the function ConfigFileHasValidEntries::run() with the HasKey check.
     *
     * @dataProvider filesHasKeyProvider
     *
     * @param ConfigFileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param int $actionSeverity,
     * @param bool $expected
     * @param $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKey(
        ConfigFileHasValidEntries $fileHasValidEntries,
        string $key,
        int $actionSeverity,
        bool $expected,
        $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries
                ->hasKey($key)
                ->setActionSeverity($actionSeverity)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function ConfigFileHasValidEntries::isValid().
     *
     * @dataProvider isValidProvider
     *
     * @param string $filePath
     * @param string $contentType
     * @param mixed $verifyKey
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ValidationException
     */
    public function testIsValid(
        string $filePath,
        string $contentType,
        $verifyKey,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        $fileHasValidEntries = ActionFactory::createConfigFileHasValidEntries($filePath, $contentType);
        if (is_string($verifyKey)) {
            $fileHasValidEntries->hasKey($verifyKey);
        }
        if ($exceptionExpected) {
            $this->expectException(ValidationException::class);
        }
        $this->assertEquals($expected, $fileHasValidEntries->isValid());
    }

    /**
     * If we try to parse an empty json file (created with wrong syntax use), we should get a RuntimeException.
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithEmptyFile(): void
    {
        // An exception should be thrown here because the file is empty and it is decoded to an empty string
        $this->expectException(WorkerException::class);
        ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_EMPTY)
            ->contentType(FileUtils::CONTENT_TYPE_JSON)->hasKey('test1')
            ->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)
            ->run();
    }

    /**
     * Test the function ConfigFileHasValidEntries::run() with the DoesNotHasKey check.
     *
     * @dataProvider filesDoesNotHaveKeyProvider
     *
     * @param ConfigFileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param int $actionSeverity
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunDoesNotHaveKey(
        ConfigFileHasValidEntries $fileHasValidEntries,
        string $key,
        int $actionSeverity,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries
                ->doesNotHaveKey($key)
                ->setActionSeverity($actionSeverity)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function ConfigFileHasValidEntries::run() with the KeyWithEmptyValue check.
     *
     * @dataProvider filesHasKeyWithEmptyValueProvider
     *
     * @param ConfigFileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param int $actionSeverity
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithEmptyValue(
        ConfigFileHasValidEntries $fileHasValidEntries,
        string $key,
        int $actionSeverity,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries
                ->hasKeyWithEmptyValue($key)
                ->setActionSeverity($actionSeverity)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function ConfigFileHasValidEntries::run() with the KeyWithNonEmptyValue check.
     *
     * @dataProvider filesHasKeyWithNonEmptyValueProvider
     *
     * @param ConfigFileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param int $actionSeverity
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithNonEmptyValue(
        ConfigFileHasValidEntries $fileHasValidEntries,
        string $key,
        int $actionSeverity,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries
                ->hasKeyWithNonEmptyValue($key)
                ->setActionSeverity($actionSeverity)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the function ConfigFileHasValidEntries::run() with the KeyWithValue check.
     *
     * @dataProvider filesHasKeyWithValueProvider
     *
     * @param ConfigFileHasValidEntries $fileHasValidEntries
     * @param string $key
     * @param mixed $value
     * @param string $compareActionType
     * @param int $actionSeverity
     * @param bool $expected
     * @param bool $exceptionExpected
     * @param bool $caseSensitive
     *
     * @throws ActionException
     */
    public function testRunHasKeyWithValue(
        ConfigFileHasValidEntries $fileHasValidEntries,
        string $key,
        $value,
        string $compareActionType,
        int $actionSeverity,
        bool $expected,
        bool $exceptionExpected,
        bool $caseSensitive = false
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            $fileHasValidEntries
                ->hasKeyWithValue($key, $value, $compareActionType, $caseSensitive)
                ->setActionSeverity($actionSeverity)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test method ConfigFileHasValidEntries::stringify().
     *
     * @dataProvider stringifyProvider
     * @dataProvider stringifyCaseInsensitiveProvider
     *
     * @param ConfigFileHasValidEntries $fileHasValidEntries
     * @param string $expected
     */
    public function testStringify(ConfigFileHasValidEntries $fileHasValidEntries, string $expected): void
    {
        $this->stringifyTest($expected, $fileHasValidEntries);
    }

    /**
     * @return array
     */
    protected function getFileHasValidEntriesInstances(): array
    {
        return [
            ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_JSON, FileUtils::CONTENT_TYPE_JSON),
            ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_ARRAY, FileUtils::CONTENT_TYPE_ARRAY),
            ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_INI, FileUtils::CONTENT_TYPE_INI),
            ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_XML, FileUtils::CONTENT_TYPE_XML),
            ActionFactory::createConfigFileHasValidEntries(self::TEST_FILE_TMP_YAML, FileUtils::CONTENT_TYPE_YAML),
        ];
    }
}
