<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class MoveDirectoryTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
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
            // source | target | is valid | severity | expected | exception | message
            [self::TEST_SOURCE_DIRECTORY_TMP, self::TEST_TARGET_DIRECTORY_TMP, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            ['', self::TEST_TARGET_DIRECTORY_TMP, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Move directory '' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Move directory '' to ''."],
            /** fatal */
            ['', self::TEST_TARGET_DIRECTORY_TMP, false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Move directory '' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Move directory '' to ''."],
            /** success required */
//TODO MISSING TESTS FOR SUCCESS REQUIRED
            /** success required */
            ['', self::TEST_TARGET_DIRECTORY_TMP, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Move directory '' to '".self::TEST_TARGET_DIRECTORY_TMP."'."],
            [self::TEST_SOURCE_DIRECTORY_TMP, '', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Move directory '".self::TEST_SOURCE_DIRECTORY_TMP."' to ''."],
            ['', '', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Move directory '' to ''."],
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
        $this->isValidTest($isValid, WorkerActionFactory::createMoveDirectory($sourcePath, $targetPath));
    }

    /**
     * Test method MoveDirectory::stringify().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $sourcePath,
        string $targetPath,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, WorkerActionFactory::createMoveDirectory($sourcePath, $targetPath));
    }

    /**
     * Test method MoveDirectory::run().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        string $sourcePath,
        string $targetPath,
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
            WorkerActionFactory::createMoveDirectory()
                ->move($sourcePath)
                ->to($targetPath)
                ->setActionSeverity($actionSeverity),
            $expected
        );

        // The directory has been moved, then we check if the original
        // directory is not in the file system anymore
        if ($expected) {
            $this->assertDirectoryExists($targetPath);
            $this->assertDirectoryDoesNotExist($sourcePath);
        }
    }
}