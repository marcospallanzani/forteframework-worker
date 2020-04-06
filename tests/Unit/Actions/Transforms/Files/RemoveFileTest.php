<?php
/**
 * This file is part of the ForteFramework package.
 *
 * Copyright (c) 2019  Marco Spallanzani <marco@forteframework.com>
 *
 *  For the full copyright and license information,
 *  please view the LICENSE file that was distributed
 *  with this source code.
 */

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Transforms\Files\RemoveFile;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class RemoveFileTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
 */
class RemoveFileTest extends BaseTest
{
    /**
     * Temporary files constants.
     */
    const TEST_DIR_TMP    = __DIR__ . '/todelete';
    const TEST_FILE_TXT   = self::TEST_DIR_TMP . '/file-tests-template.txt';
    const TEST_FILE_JSON  = self::TEST_DIR_TMP . '/file-tests-template.json';
    const TEST_WRONG_FILE = "/path/to/non/existent/file.php";
    const TEST_CONTENT    = "ANY CONTENT";

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        // We have to copy the template file, which will be deleted by this test
        if (!is_dir(self::TEST_DIR_TMP)) {
            @mkdir(self::TEST_DIR_TMP);
        }
        @file_put_contents(self::TEST_FILE_TXT, self::TEST_CONTENT);
        @file_put_contents(self::TEST_FILE_JSON, self::TEST_CONTENT);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_FILE_TXT);
        @unlink(self::TEST_FILE_JSON);
        @rmdir(self::TEST_DIR_TMP);
    }

    /**
     * Data provider for all files tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            // File path | is valid | expected result | severity | exception expected | remove mode | expected stringify message
            [self::TEST_FILE_TXT, true, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, RemoveFile::REMOVE_SINGLE_FILE, "Remove file '" . self::TEST_FILE_TXT . "'."],
            [self::TEST_FILE_JSON, true, true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, RemoveFile::REMOVE_SINGLE_FILE, "Remove file '" . self::TEST_FILE_JSON . "'."],
            /** Negative cases */
            /** not successful, no fatal */
            [self::TEST_WRONG_FILE, true, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, RemoveFile::REMOVE_SINGLE_FILE, "Remove file '" . self::TEST_WRONG_FILE . "'."],
            [self::TEST_DIR_TMP, true, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, RemoveFile::REMOVE_DIRECTORY, "Remove directory '" . self::TEST_DIR_TMP . "'."],
            ['', false, false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, RemoveFile::REMOVE_SINGLE_FILE, "Remove file ''."],
            /** not successful, fatal */
            [self::TEST_WRONG_FILE, true, false, ActionInterface::EXECUTION_SEVERITY_FATAL, true, RemoveFile::REMOVE_SINGLE_FILE, "Remove file '" . self::TEST_WRONG_FILE . "'."],
            ['', false, false, ActionInterface::EXECUTION_SEVERITY_FATAL, true, RemoveFile::REMOVE_SINGLE_FILE, "Remove file ''."],
            /** successful with negative result, is success required */
//TODO IMPLEMENT MISSING TEST CASES
            /** critical (errors or negative result -> exception)*/
            [self::TEST_WRONG_FILE, true, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, true, RemoveFile::REMOVE_SINGLE_FILE, "Remove file '" . self::TEST_WRONG_FILE . "'."],
            ['', false, false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, true, RemoveFile::REMOVE_SINGLE_FILE, "Remove file ''."],
        ];
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * for one single file.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $isValid
     * @param bool $expected
     * @param int $actionSeverity
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRemoveFile(
        string $filePath,
        bool $isValid,
        bool $expected,
        int $actionSeverity,
        bool $exceptionExpected
    ): void
    {
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            WorkerActionFactory::createRemoveFile()
                ->removeFile($filePath)
                ->addBeforeAction(WorkerActionFactory::createFileExists($filePath))
                ->addAfterAction(WorkerActionFactory::createFileDoesNotExist($filePath))
                ->setActionSeverity($actionSeverity),
            $expected
        );
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * for one single file without the pre- and post-transform checks.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $isValid
     * @param bool $expected
     * @param int $actionSeverity
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRemoveFileNoChecks(
        string $filePath,
        bool $isValid,
        bool $expected,
        int $actionSeverity,
        bool $exceptionExpected
    ): void
    {
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            WorkerActionFactory::createRemoveFile()
                ->removeFile($filePath)
                ->setActionSeverity($actionSeverity),
            $expected
        );
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * in mode "single-file", but with a folder as a parameter, in "FATAL"
     * mode.
     *
     * @throws ActionException
     */
    public function testRemoveFileDirectoryException(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $this->expectException(ActionException::class);
        WorkerActionFactory::createRemoveFile()->removeFile(self::TEST_DIR_TMP)->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)->run();
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * in mode "single-file", but with a folder as a parameter, in "NON FATAL"
     * mode.
     *
     * @throws ActionException
     */
    public function testRemoveFileDirectoryNegativeResult(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $actionResult = WorkerActionFactory::createRemoveFile()->removeFile(self::TEST_DIR_TMP)->run();
        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $this->assertEmpty($actionResult->getResult());
        $this->assertCount(1, $actionResult->getActionFailures());
        $this->assertInstanceOf(ActionException::class, current($actionResult->getActionFailures()));
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * in mode "directory-file", with a valid folder parameter.
     *
     * @throws ActionException
     */
    public function testRemoveDirectory(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $this->assertTrue(
            WorkerActionFactory::createRemoveFile()
                ->removeDirectory(self::TEST_DIR_TMP)
                ->addBeforeAction(WorkerActionFactory::createDirectoryExists(self::TEST_DIR_TMP))
                ->addAfterAction(WorkerActionFactory::createDirectoryDoesNotExist(self::TEST_DIR_TMP))
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * in mode "directory-file", with a valid folder parameter.
     *
     * @throws ActionException
     */
    public function testRemoveDirectoryWithFilePatter(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $this->assertTrue(
            WorkerActionFactory::createRemoveFile()
                ->removeFilePattern(self::TEST_DIR_TMP . '/*json')
                ->addBeforeAction(WorkerActionFactory::createFileExists(self::TEST_FILE_JSON))
                ->addAfterAction(WorkerActionFactory::createFileDoesNotExist(self::TEST_FILE_JSON))
                ->run()
                ->getResult()
        );
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * in mode "directory-file", but with a wrong or non-existing folder as
     * a parameter, and in "FATAL" mode.
     *
     * @throws ActionException
     */
    public function testRemoveDirectoryExpectException(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $this->expectException(ActionException::class);
        WorkerActionFactory::createRemoveFile()->removeFile(__DIR__ . '/files/xxx/')->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)->run();
    }

    /**
     * Test the Forte\Worker\Actions\Transforms\Files\Remove::run() method
     * in mode "directory-file", but with a wrong or non-existing folder as
     * a parameter, and in "NON FATAL" mode.
     *
     * @throws ActionException
     */
    public function testRemoveDirectoryNegativeResult(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $actionResult = WorkerActionFactory::createRemoveFile()->removeFile(__DIR__ . '/files/xxx/')->run();
        $this->assertInstanceOf(ActionResult::class, $actionResult);
        $this->assertEmpty($actionResult->getResult());
        $this->assertCount(1, $actionResult->getActionFailures());
        $this->assertInstanceOf(ActionException::class, current($actionResult->getActionFailures()));

    }

    /**
     * Test method RemoveFile::isValid().
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $isValid
     * @param bool $expected
     * @param int $actionSeverity
     * @param bool $exceptionExpected
     * @param string $removeMode
     *
     * @throws ValidationException
     */
    public function testIsValid(
        string $filePath,
        bool $isValid,
        bool $expected,
        int $actionSeverity,
        bool $exceptionExpected,
        string $removeMode
    ): void
    {
        $this->isValidTest($isValid, WorkerActionFactory::createRemoveFile()->remove($filePath, $removeMode));
    }

    /**
     * Test method Remove::stringify().
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $isValid
     * @param bool $expected
     * @param int $actionSeverity
     * @param bool $exceptionExpected
     * @param string $mode
     * @param string $message
     */
    public function testStringify(
        string $filePath,
        bool $isValid,
        bool $expected,
        int $actionSeverity,
        bool $exceptionExpected,
        string $mode,
        string $message
    ): void
    {
        $this->stringifyTest($message, WorkerActionFactory::createRemoveFile()->remove($filePath, $mode));
    }
}
