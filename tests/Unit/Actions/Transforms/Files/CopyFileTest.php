<?php

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class CopyFileTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
 */
class CopyFileTest extends BaseTest
{
    /**
     * Test constants.
     */
    const TEST_SOURCE_NAME              = 'file-to-copy.json';
    const TEST_COPIED_SOURCE_NAME       = 'file-to-copy_COPY.json';
    const TEST_TARGET_FILE              = 'file-custom-name.json';
    const TEST_SOURCE_DIR               = __DIR__;
    const TEST_TARGET_DIR               = __DIR__ . DIRECTORY_SEPARATOR . "copied-files";
    // File to copy
    const TEST_COPY_FILE_PATH           = self::TEST_SOURCE_DIR . DIRECTORY_SEPARATOR . self:: TEST_SOURCE_NAME;
    // Target file path same name, different directory
    const TEST_FILE_DIR_PATH            = self::TEST_TARGET_DIR . DIRECTORY_SEPARATOR . self::TEST_SOURCE_NAME;
    // Target file path custom name, different directory
    const TEST_FILE_CUSTOM_NAME         = self::TEST_TARGET_DIR . DIRECTORY_SEPARATOR . self::TEST_TARGET_FILE;
    // Target file path custom name, same directory
    const TEST_FILE_CUSTOM_NAME_IN_DIR  = self::TEST_SOURCE_DIR . DIRECTORY_SEPARATOR . self::TEST_TARGET_FILE;
    // Target file path modified name, same directory
    const TEST_COPY_FILE_DEFAULT_PATH   = self::TEST_SOURCE_DIR . DIRECTORY_SEPARATOR . self:: TEST_COPIED_SOURCE_NAME;

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        if (!is_dir(self::TEST_TARGET_DIR)) {
            @mkdir(self::TEST_TARGET_DIR);
        }
        @file_put_contents(self::TEST_COPY_FILE_PATH, '');
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_COPY_FILE_PATH);
        @unlink(self::TEST_FILE_DIR_PATH);
        @unlink(self::TEST_FILE_CUSTOM_NAME);
        @unlink(self::TEST_COPY_FILE_DEFAULT_PATH);
        @unlink(self::TEST_FILE_CUSTOM_NAME_IN_DIR);
        @rmdir(self::TEST_TARGET_DIR);
    }

    /**
     * Data provider for all copy-file tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        // source path | target dir | target name | is valid | severity | expected result | exception expected
        return [
            [self::TEST_COPY_FILE_PATH, "", self::TEST_TARGET_FILE, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, sprintf("Copy file '%s' to '%s'.", self::TEST_COPY_FILE_PATH, self::TEST_FILE_CUSTOM_NAME_IN_DIR)],
            [self::TEST_COPY_FILE_PATH, self::TEST_TARGET_DIR, self::TEST_TARGET_FILE, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, sprintf("Copy file '%s' to '%s'.", self::TEST_COPY_FILE_PATH, self::TEST_FILE_CUSTOM_NAME)],
            [self::TEST_COPY_FILE_PATH, self::TEST_TARGET_DIR, "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, sprintf("Copy file '%s' to '%s'.", self::TEST_COPY_FILE_PATH, self::TEST_TARGET_DIR . DIRECTORY_SEPARATOR . self::TEST_SOURCE_NAME)],
            [self::TEST_COPY_FILE_PATH, self::TEST_SOURCE_DIR, "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, sprintf("Copy file '%s' to '%s'.", self::TEST_COPY_FILE_PATH, self::TEST_COPY_FILE_DEFAULT_PATH)],
            [self::TEST_COPY_FILE_PATH, "", "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false, sprintf("Copy file '%s' to '%s'.", self::TEST_COPY_FILE_PATH, self::TEST_COPY_FILE_DEFAULT_PATH)],
            /** Negative cases */
            /** not successful, no fatal */
            ["xxx.json", "", "xxx_copy.json", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Copy file 'xxx.json' to './xxx_copy.json'."],
            ["xxx.json", "", "", true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Copy file 'xxx.json' to './xxx_COPY.json'."],
            ["", "", "", false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, "Copy file '' to ''."],
            /** fatal */
            ["xxx.json", "", "xxx_copy.json", true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Copy file 'xxx.json' to './xxx_copy.json'."],
            ["xxx.json", "", "", true, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Copy file 'xxx.json' to './xxx_COPY.json'."],
            ["", "", "", false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, "Copy file '' to ''."],
//TODO THIS SHOULD BE DONE WITH MOCK OBJECTS
            /** success required */
            ["xxx.json", "", "xxx_copy.json", true, ActionInterface::EXECUTION_SEVERITY_SUCCESS_REQUIRED, false, false, "Copy file 'xxx.json' to './xxx_copy.json'."],
            ["xxx.json", "", "", true, ActionInterface::EXECUTION_SEVERITY_SUCCESS_REQUIRED, false, false, "Copy file 'xxx.json' to './xxx_COPY.json'."],
            ["", "", "", false, ActionInterface::EXECUTION_SEVERITY_SUCCESS_REQUIRED, false, false, "Copy file '' to ''."],
//TODO THIS SHOULD BE DONE WITH MOCK OBJECTS
            /** critical */
            ["xxx.json", "", "xxx_copy.json", true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Copy file 'xxx.json' to './xxx_copy.json'."],
            ["xxx.json", "", "", true, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Copy file 'xxx.json' to './xxx_COPY.json'."],
            ["", "", "", false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, "Copy file '' to ''."],
        ];
    }

    /**
     * Test method CopyFile::isValid().
     *
     * @dataProvider filesProvider
     *
     * @param string $sourcePath
     * @param string $targetDir
     * @param string $targetName
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(string $sourcePath, string $targetDir, string $targetName, bool $isValid): void
    {
        $action = $this->getConfiguredAction($sourcePath, $targetDir, $targetName);
        $this->isValidTest($isValid, $action);
    }

    /**
     * Test method CopyFile::stringify().
     *
     * @dataProvider filesProvider
     *
     * @param string $sourcePath
     * @param string $targetDir
     * @param string $targetName
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $sourcePath,
        string $targetDir,
        string $targetName,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $action = $this->getConfiguredAction($sourcePath, $targetDir, $targetName);
        $this->stringifyTest($message, $action);
    }

    /**
     * Test method CopyFile::run().
     *
     * @dataProvider filesProvider
     *
     * @param string $sourcePath
     * @param string $targetDir
     * @param string $targetName
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException An error occurred while copying the file.
     */
    public function testRun(
        string $sourcePath,
        string $targetDir,
        string $targetName,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected
    ): void
    {
        $action = $this->getConfiguredAction($sourcePath, $targetDir, $targetName);
        $action->setActionSeverity($actionSeverity);

        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            $action,
            $expected
        );

        // The file has been moved, then we check if the original
        // file is not in the file system anymore
        if ($expected) {
            $this->assertFileExists($action->getDestinationFilePath());
        }
    }

    /**
     * Return a configured CopyFile instance.
     *
     * @param string $sourcePath
     * @param string $targetDir
     * @param string $targetName
     *
     * @return CopyFile
     */
    protected function getConfiguredAction(string $sourcePath, string $targetDir, string $targetName): CopyFile
    {
        $action = WorkerActionFactory::createCopyFile()->copy($sourcePath);
        if (!empty($targetDir)) {
            $action->toFolder($targetDir);
        }
        if (!empty($targetName)) {
            $action->withName($targetName);
        }
        return $action;
    }
}
