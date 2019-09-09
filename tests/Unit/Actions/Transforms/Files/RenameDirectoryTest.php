<?php

namespace Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\Transforms\Files\RenameDirectory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Tests\Unit\BaseTest;

/**
 * Class RenameDirectoryTest.
 *
 * @package Tests\Unit\Actions\Transforms\Files
 */
class RenameDirectoryTest extends BaseTest
{
    /**
     * Temporary directory constants.
     */
    const TEST_SOURCE_DIRECTORY_TMP = __DIR__ . '/directory-to-rename';
    const TEST_TARGET_DIRECTORY_TMP = 'directory-renamed';
    const TEST_TARGET_PATH_TMP      = __DIR__ . '/' . self::TEST_TARGET_DIRECTORY_TMP;

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
        @rmdir(self::TEST_TARGET_PATH_TMP);
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
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_DIRECTORY_TMP, true, false, false, true, false, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_PATH_TMP, false, false, false, false, false, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, false, false, false, false, "Rename directory '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, false, false, false, false, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, false, false, false, false, "Rename directory '' to ''."],
            /** fatal */
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_PATH_TMP, false, true, false, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, true, false, false, true, "Rename directory '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, true, false, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, true, false, false, true, "Rename directory '' to ''."],
            /** success required */
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_PATH_TMP, false, false, true, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, false, true, false, true, "Rename directory '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, false, true, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, false, true, false, true, "Rename directory '' to ''."],
        ];
    }

    /**
     * Test method RenameDirectory::isValid().
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
        $this->isValidTest($isValid, new RenameDirectory($sourcePath, $targetName));
    }

    /**
     * Test method RenameDirectory::stringify().
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
        $this->stringifyTest($message, new RenameDirectory($sourcePath, $targetName));
    }

    /**
     * Test method RenameDirectory::run().
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
        bool $exceptionExpected,
        string $message
    ): void
    {
        $renameDirectory =
            (new RenameDirectory())
                ->rename($sourcePath)
                ->to($targetName)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
        ;

        // Basic checks
        $this->runBasicTest($exceptionExpected, $isValid, $renameDirectory, $expected);

        // The directory has been renamed, then we check if the original file is not in the file system anymore
        if ($expected) {
            $this->assertDirectoryExists(dirname($sourcePath) . DIRECTORY_SEPARATOR . $targetName);
            $this->assertDirectoryNotExists($sourcePath);
        }
    }
}
