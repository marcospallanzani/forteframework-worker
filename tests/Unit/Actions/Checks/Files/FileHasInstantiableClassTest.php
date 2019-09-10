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

namespace Tests\Unit\Actions\Checks\Files;

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Tests\Unit\BaseTest;

/**
 * Class FileHasInstantiableClassTest
 *
 * @package Tests\Unit\Actions\Checks\Files
 */
class FileHasInstantiableClassTest extends BaseTest
{
    /**
     * Temporary files constants
     */
    const TEST_FILE_TMP = __DIR__ . '/file-tests';
    const TEST_CONTENT  = "ANY CONTENT";

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        @file_put_contents(self::TEST_FILE_TMP, self::TEST_CONTENT);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_FILE_TMP);
    }

    /**
     * Data provider for isValid() tests.
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function validationProvider(): array
    {
        $selfClass = (new \ReflectionClass(get_called_class()))->getShortName();

        return [
            ["", "", false, true, "Check if file '' has an instatiable class."],
            ["", $selfClass, false, true, "Check if file '' has the class '$selfClass'."],
            [__FILE__, "", true, false, "Check if file '".__FILE__."' has an instatiable class."],
            [__FILE__, $selfClass, true, false, "Check if file '".__FILE__."' has the class '$selfClass'."],
            [self::TEST_FILE_TMP, $selfClass, true, false, "Check if file '".self::TEST_FILE_TMP."' has the class '$selfClass'."],
            [__FILE__, "test", true, false, "Check if file '".__FILE__."' has the class 'test'."],
        ];
    }

    /**
     * Data provider for run() tests.
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    public function runProvider(): array
    {
        $selfClass = (new \ReflectionClass(get_called_class()))->getShortName();

        return [
            ["", "", true, false, false, true],
            ["", $selfClass, true, false, false, true],
            [__FILE__, "", false, false, true, false],
            [__FILE__, $selfClass, false, false, true, false],
            [self::TEST_FILE_TMP, $selfClass, false, false, false, false],
            [__FILE__, "test", false, false, false, false],
        ];
    }

    /**
     * Test method FileHasInstantiableClass::isValid().
     *
     * @dataProvider validationProvider
     *
     * @param string $filePath
     * @param string $className
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ValidationException
     */
    public function testIsValid(string $filePath, string $className, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(ValidationException::class);
        }
        $this->assertEquals(
            $expected,
            ActionFactory::createFileHasInstantiableClass($filePath, $className)->isValid()
        );
    }


    /**
     * Test method FileHasInstantiableClass::run().
     *
     * @dataProvider runProvider
     * @depends      testIsValid
     *
     * @param string $filePath
     * @param string $className
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testCheckFileHasClass(
        string $filePath,
        string $className,
        bool $isFatal,
        bool $isSuccessRequired,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            ActionFactory::createFileHasInstantiableClass($filePath, $className)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired)
                ->run()
                ->getResult()
        );
    }

    /**
     * Test method FileHasInstantiableClass::stringify().
     *
     * @dataProvider validationProvider
     *
     * @param string $filePath
     * @param string $className
     * @param bool $expected
     * @param bool $exceptionExpected
     * @param string $stringified
     */
    public function testStringify(
        string $filePath,
        string $className,
        bool $expected,
        bool $exceptionExpected,
        string $stringified
    ): void
    {
        $this->stringifyTest(
            $stringified,
            ActionFactory::createFileHasInstantiableClass()
                ->setClass($className)
                ->setPath($filePath)
        );
    }
}
