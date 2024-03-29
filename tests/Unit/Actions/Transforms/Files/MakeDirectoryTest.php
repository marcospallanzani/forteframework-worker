<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class MakeDirectoryTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
 */
class MakeDirectoryTest extends BaseTest
{
    /**
     * Test constants.
     */
    const TEST_DIR_TMP       = __DIR__ . '/tomake';
    const TEST_DIR_EXIST_TMP = __DIR__ . '/already-made';

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        // We have to copy the template file, which will be deleted by this test
        if (is_dir(self::TEST_DIR_TMP)) {
            @rmdir(self::TEST_DIR_TMP);
        }

        if (is_dir(self::TEST_DIR_EXIST_TMP)) {
            @rmdir(self::TEST_DIR_EXIST_TMP);
        }
        @mkdir(self::TEST_DIR_EXIST_TMP);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @rmdir(self::TEST_DIR_TMP);
        @rmdir(self::TEST_DIR_EXIST_TMP);
    }

    /**
     * Data provider for all directory tests.
     *
     * @return array
     */
    public function directoryProvider(): array
    {
        return [
            // directory path | is valid | severity | expected | exception | message
            [self::TEST_DIR_TMP, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, "Create directory '".self::TEST_DIR_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            ['', false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Create directory ''."],
            [self::TEST_DIR_EXIST_TMP, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Create directory '".self::TEST_DIR_EXIST_TMP."'."],
            /** fatal */
            ['', false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Create directory ''."],
            [self::TEST_DIR_EXIST_TMP, true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Create directory '".self::TEST_DIR_EXIST_TMP."'."],
            /** success required */
//TODO
            /** critical */
            ['', false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Create directory ''."],
            [self::TEST_DIR_EXIST_TMP, true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Create directory '".self::TEST_DIR_EXIST_TMP."'."],
        ];
    }

    /**
     * Test method MakeDirectory::isValid().
     *
     * @dataProvider directoryProvider
     *
     * @param string $sourcePath
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(string $sourcePath, bool $isValid): void
    {
        $this->isValidTest($isValid, WorkerActionFactory::createMakeDirectory($sourcePath));
    }

    /**
     * Test method MakeDirectory::stringify().
     *
     * @dataProvider directoryProvider
     *
     * @param string $directoryPath
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $directoryPath,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, WorkerActionFactory::createMakeDirectory($directoryPath));
    }

    /**
     * Test method MakeDirectory::run().
     *
     * @dataProvider directoryProvider
     *
     * @param string $directoryPath
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        string $directoryPath,
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
            WorkerActionFactory::createMakeDirectory()
                ->create($directoryPath)
                ->setActionSeverity($actionSeverity),
            $expected
        );

        // We check if the directory has been created
        if ($expected) {
            $this->assertDirectoryExists($directoryPath);
        }
    }
}
