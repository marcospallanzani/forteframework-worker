<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
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
            // source | target | is valid | severity | expected | exception | message
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_NAME_TMP, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_NAME_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename file '' to ''."],
            /** fatal */
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename file '' to ''."],
            /** success required */
//TODO MISSING TEST CASES
            /** critical */
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename file '' to ''."],
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
        $this->isValidTest($isValid, WorkerActionFactory::createRenameFile($sourcePath, $targetName));
    }

    /**
     * Test method RenameFile::stringify().
     *
     * @dataProvider renameProvider
     *
     * @param string $sourcePath
     * @param string $targetName
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $sourcePath,
        string $targetName,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, WorkerActionFactory::createRenameFile($sourcePath, $targetName));
    }

    /**
     * Test method RenameFile::run().
     *
     * @dataProvider renameProvider
     *
     * @param string $sourcePath
     * @param string $targetName
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        string $sourcePath,
        string $targetName,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected
    ): void
    {
        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            WorkerActionFactory::createRenameFile()
                ->rename($sourcePath)
                ->to($targetName)
                ->setActionSeverity($actionSeverity),
            $expected
        );

        // The file has been renamed, then we check if the original file is not in the file system anymore
        if ($expected) {
            $this->assertFileExists(dirname($sourcePath) . DIRECTORY_SEPARATOR . $targetName);
            $this->assertFileNotExists($sourcePath);
        }
    }
}
