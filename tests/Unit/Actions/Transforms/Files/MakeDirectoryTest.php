<?php

namespace Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Tests\Unit\BaseTest;

/**
 * Class MakeDirectoryTest.
 *
 * @package Tests\Unit\Actions\Transforms\Files
 */
class MakeDirectoryTest extends BaseTest
{
    /**
     * Test constants.
     */
    const TEST_DIR_TMP    = __DIR__ . '/tomake';

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
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @rmdir(self::TEST_DIR_TMP);
    }

    /**
     * Data provider for all directory tests.
     *
     * @return array
     */
    public function directoryProvider(): array
    {
        return [
            // directory path | is valid | fatal | success required | expected | exception | message
            [self::TEST_DIR_TMP, true, false, false, true, false, "Create directory '".self::TEST_DIR_TMP."'."],
            /** Negative cases */
            /** not successful, no fatal */
            ['', false, false, false, false, false, "Create directory ''."],
            /** fatal */
            ['', false, true, false, false, true, "Create directory ''."],
            /** success required */
            ['', false, false, true, false, true, "Create directory ''."],
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
        $this->isValidTest($isValid, ActionFactory::createMakeDirectory($sourcePath));
    }

    /**
     * Test method MakeDirectory::stringify().
     *
     * @dataProvider directoryProvider
     *
     * @param string $directoryPath
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        string $directoryPath,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, ActionFactory::createMakeDirectory($directoryPath));
    }

    /**
     * Test method MakeDirectory::run().
     *
     * @dataProvider directoryProvider
     *
     * @param string $directoryPath
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        string $directoryPath,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected
    ): void
    {
        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            ActionFactory::createMakeDirectory()
                ->create($directoryPath)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired),
            $expected
        );

        // We check if the directory has been created
        if ($expected) {
            $this->assertDirectoryExists($directoryPath);
        }
    }
}
