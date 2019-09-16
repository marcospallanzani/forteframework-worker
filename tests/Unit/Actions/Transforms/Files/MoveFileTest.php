<?php

namespace Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Factories\ActionFactory;
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
    const TEST_SOURCE_FILE_NAME_TMP = '/file-to-move';
    const TEST_SOURCE_FILE_TMP      = __DIR__ . self::TEST_SOURCE_FILE_NAME_TMP  ;
    const TEST_TARGET_PATH_TMP      = __DIR__ . '/file-moved';
    const TEST_TARGET_DIR_TMP       = __DIR__ . '/dir-file-moved';

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
        if (is_dir(self::TEST_TARGET_DIR_TMP)) {
            @rmdir(self::TEST_TARGET_DIR_TMP);
        }
    }

    /**
     * Data provider for all move tests.
     *
     * @return array
     */
    public function moveProvider(): array
    {
        return [
            // source | target | full target path | is valid | fatal | success required | expected | exception | message
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_PATH_TMP, true, true, false, false, true, false, "Move file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, self::TEST_TARGET_DIR_TMP, false, true, false, false, true, false, "Move file '".self::TEST_SOURCE_FILE_TMP."' to '".self::TEST_TARGET_DIR_TMP.self::TEST_SOURCE_FILE_NAME_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            ['', self::TEST_TARGET_PATH_TMP, true, false, false, false, false, false, "Move file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', true, false, false, false, false, false, "Move file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', true, false, false, false, false, false, "Move file '' to ''."],
            /** fatal */
            ['', self::TEST_TARGET_PATH_TMP, true, false, true, false, false, true, "Move file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', true, false, true, false, false, true, "Move file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', true, false, true, false, false, true, "Move file '' to ''."],
            /** success required */
            ['', self::TEST_TARGET_PATH_TMP, true, false, false, true, false, true, "Move file '' to '".self::TEST_TARGET_PATH_TMP."'."],
            [self::TEST_SOURCE_FILE_TMP, '', true, false, false, true, false, true, "Move file '".self::TEST_SOURCE_FILE_TMP."' to ''."],
            ['', '', true, false, false, true, false, true, "Move file '' to ''."],
        ];
    }

    /**
     * Test method MoveFile::isValid().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isFullTargetPath
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(string $sourcePath, string $targetPath, bool $isFullTargetPath, bool $isValid): void
    {
        $action = ActionFactory::createMoveFile($sourcePath);

        $this->setTarget($action, $isFullTargetPath, $targetPath);

        $this->isValidTest($isValid, ActionFactory::createMoveFile($sourcePath, $targetPath));
    }

    /**
     * Test method MoveFile::stringify().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isFullTargetPath
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
        bool $isFullTargetPath,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $action = ActionFactory::createMoveFile($sourcePath);

        $this->setTarget($action, $isFullTargetPath, $targetPath);

        $this->stringifyTest($message, $action);
    }

    /**
     * Test method MoveFile::run().
     *
     * @dataProvider moveProvider
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param bool $isFullTargetPath
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
        string $targetPath,
        bool $isFullTargetPath,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected
    ): void
    {
        $action = ActionFactory::createMoveFile()
            ->move($sourcePath)
            ->setIsFatal($isFatal)
            ->setIsSuccessRequired($isSuccessRequired)
        ;

        $this->setTarget($action, $isFullTargetPath, $targetPath);

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
            $this->assertFileExists($targetPath);
            $this->assertFileNotExists($sourcePath);
        }
    }

    /**
     * Set the target path in the given action.
     *
     * @param AbstractAction $action
     * @param bool $isFullTargetPath
     * @param string $targetPath
     */
    protected function setTarget(AbstractAction &$action, bool $isFullTargetPath, string $targetPath): void
    {
        if ($isFullTargetPath) {
            $action->to($targetPath);
        } else {
            $action->toFolder($targetPath);
        }
    }
}