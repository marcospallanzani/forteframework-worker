<?php

namespace Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\Transforms\Files\MoveDirectory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Tests\Unit\BaseTest;

/**
 * Class MoveDirectoryTest.
 *
 * @package Tests\Unit\Actions\Transforms\Files
 */
class MoveDirectoryTest extends BaseTest
{
    /**
     * Temporary directory constants.
     */
    const TEST_SOURCE_DIRECTORY_TMP = __DIR__ . '/directory-to-move';
    const TEST_TARGET_DIRECTORY_TMP = __DIR__ . '/directory-moved';

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        @mkdir(self::TEST_SOURCE_DIRECTORY_TMP);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @rmdir(self::TEST_SOURCE_DIRECTORY_TMP);
        @rmdir(self::TEST_TARGET_DIRECTORY_TMP);
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
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_DIRECTORY_TMP, true, false, false, true, false, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            ['', self::TEST_TARGET_DIRECTORY_TMP, false, false, false, false, false, "Move directory '' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, false, false, false, false, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, false, false, false, false, "Move directory '' to ''."],
            /** fatal */
            ['', self::TEST_TARGET_DIRECTORY_TMP, false, true, false, false, true, "Move directory '' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, true, false, false, true, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, true, false, false, true, "Move directory '' to ''."],
            /** success required */
            ['', self::TEST_TARGET_DIRECTORY_TMP, false, false, true, false, true, "Move directory '' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, false, true, false, true, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, false, true, false, true, "Move directory '' to ''."],
        ];
    }

    /**
     * Test method MoveDirectory::isValid().
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
        $this->isValidTest($isValid, new MoveDirectory($sourcePath, $targetPath));
    }

    /**
     * Test method MoveDirectory::stringify().
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
        $this->stringifyTest($message, new MoveDirectory($sourcePath, $targetPath));
    }

    /**
     * Test method MoveDirectory::run().
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
        $moveDirectory =
            (new MoveDirectory())
                ->move($sourcePath)
                ->to($targetPath)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
            ;

        // Basic checks
        $this->runBasicTest($exceptionExpected, $isValid, $moveDirectory, $expected);

        // The directory has been moved, then we check if the original
        // directory is not in the file system anymore
        if ($expected) {
            $this->assertDirectoryExists($targetPath);
            $this->assertDirectoryNotExists($sourcePath);
        }
    }
}