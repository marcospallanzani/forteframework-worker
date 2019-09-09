<?php

namespace Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\Transforms\Files\MoveFile;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Tests\Unit\BaseTest;

/**
 * Class MoveFileTest.
 *
 * @package Tests\Unit\Actions\Transforms\Files
 */
class MoveFileTest extends BaseTest
{
    /**
     * Temporary files constants.
     */
    const TEST_SOURCE_FILE_TMP = __DIR__ . '/file-to-move';
    const TEST_TARGET_PATH_TMP = __DIR__ . '/file-moved';

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
     * Data provider for all move tests.
     *
     * @return array
     */
    public function moveProvider(): array
    {
        return [
            // source | target | is valid | fatal | success required | expected | exception | message
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, true, false, false, true, false, "Move file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            ['', self::TEST_TARGET_PATH_TMP, false, false, false, false, false, "Move file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, false, false, false, false, "Move file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, false, false, false, false, "Move file '' to ''."],
            /** fatal */
            ['', self::TEST_TARGET_PATH_TMP, false, true, false, false, true, "Move file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, true, false, false, true, "Move file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, true, false, false, true, "Move file '' to ''."],
            /** success required */
            ['', self::TEST_TARGET_PATH_TMP, false, false, true, false, true, "Move file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, false, true, false, true, "Move file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, false, true, false, true, "Move file '' to ''."],
        ];
    }

    /**
     * Test method MoveFile::isValid().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(string $sourcePath, string $targetPath, bool $isValid): void
    {
        $this->isValidTest($isValid, new MoveFile($sourcePath, $targetPath));
    }

    /**
     * Test method MoveFile::stringify().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $sourcePath,
        string $targetPath,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, new MoveFile($sourcePath, $targetPath));
    }

    /**
     * Test method MoveFile::run().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     *
     * @throws ActionException
     */
    public function testRun(
        string $sourcePath,
        string $targetPath,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $moveFile =
            (new MoveFile())
                ->move($sourcePath)
                ->to($targetPath)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
            ;

        // Basic checks
        $this->runBasicTest($exceptionExpected, $isValid, $moveFile, $expected);

        // The file has been moved, then we check if the original
        // file is not in the file system anymore
        if ($expected) {
            $this->assertFileExists($targetPath);
            $this->assertFileNotExists($sourcePath);
        }
    }
}