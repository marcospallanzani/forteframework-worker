<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class RenameDirectoryTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
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
            // source | target | is valid | severity | expected | exception | message
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_DIRECTORY_TMP, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename directory '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Rename directory '' to ''."],
            /** fatal */
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename directory '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Rename directory '' to ''."],
            /** success required */
//TODO MISSING TEST CASES
            /** critical */
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            ['', self::TEST_TARGET_PATH_TMP, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename directory '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Rename directory '' to ''."],
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
        $this->isValidTest($isValid, WorkerActionFactory::createRenameDirectory($sourcePath, $targetName));
    }

    /**
     * Test method RenameDirectory::stringify().
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
        $this->stringifyTest($message, WorkerActionFactory::createRenameDirectory($sourcePath, $targetName));
    }

    /**
     * Test method RenameDirectory::run().
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
            WorkerActionFactory::createRenameDirectory()
                ->rename($sourcePath)
                ->to($targetName)
                ->setActionSeverity($actionSeverity),
            $expected
        );

        // The directory has been renamed, then we check if the original file is not in the file system anymore
        if ($expected) {
            $this->assertDirectoryExists(dirname($sourcePath) . DIRECTORY_SEPARATOR . $targetName);
            $this->assertDirectoryDoesNotExist($sourcePath);
        }
    }
}
