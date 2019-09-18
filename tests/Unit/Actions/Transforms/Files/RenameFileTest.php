<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class RenameFileTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
 */
class RenameFileTest extends BaseTest
{
    /**
     * Temporary files constants.
     */
    const TEST_SOURCE_FILE_TMP = __DIR__ . '/file-to-rename';
    const TEST_TARGET_NAME_TMP = 'file-renamed';
    const TEST_TARGET_PATH_TMP = __DIR__ . '/' . self::TEST_TARGET_NAME_TMP;

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        @file_put_contents(self::TEST_SOURCE_FILE_TMP, '');
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_SOURCE_FILE_TMP);
        @unlink(self::TEST_TARGET_PATH_TMP);
    }

    /**
     * Data provider for all rename tests.
     *
     * @return array
     */
    public function renameProvider(): array
    {
        return [
            // source | target | is valid | fatal | success required | expected | exception | message
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_NAME_TMP, true, false, false, true, false, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_NAME_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, false, false, false, false, false, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, false, false, false, false, "Rename file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, false, false, false, false, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, false, false, false, false, "Rename file '' to ''."],
            /** fatal */
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, false, true, false, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, true, false, false, true, "Rename file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, true, false, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, true, false, false, true, "Rename file '' to ''."],
            /** success required */
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, false, false, true, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, false, true, false, true, "Rename file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, false, true, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, false, true, false, true, "Rename file '' to ''."],
        ];
    }

    /**
     * Test method RenameFile::isValid().
     *
     * @dataProvider renameProvider
     *
     * @param string $sourcePath
     * @param string $targetName
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(string $sourcePath, string $targetName, bool $isValid): void
    {
        $this->isValidTest($isValid, ActionFactory::createRenameFile($sourcePath, $targetName));
    }

    /**
     * Test method RenameFile::stringify().
     *
     * @dataProvider renameProvider
     *
     * @param string $sourcePath
     * @param string $targetName
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $sourcePath,
        string $targetName,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, ActionFactory::createRenameFile($sourcePath, $targetName));
    }

    /**
     * Test method RenameFile::run().
     *
     * @dataProvider renameProvider
     *
     * @param string $sourcePath
     * @param string $targetName
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        string $sourcePath,
        string $targetName,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected
    ): void
    {
        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            ActionFactory::createRenameFile()
                ->rename($sourcePath)
                ->to($targetName)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired),
            $expected
        );

        // The file has been renamed, then we check if the original file is not in the file system anymore
        if ($expected) {
            $this->assertFileExists(dirname($sourcePath) . DIRECTORY_SEPARATOR . $targetName);
            $this->assertFileNotExists($sourcePath);
        }
    }
}
