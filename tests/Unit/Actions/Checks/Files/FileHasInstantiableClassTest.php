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

namespace Forte\Worker\Tests\Unit\Actions\Checks\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class FileHasInstantiableClassTest
 *
 * @package Forte\Worker\Tests\Unit\Actions\Checks\Files
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

        // filePath | className | actionSeverity | expected | exceptionExpected
        return [
            ["", "", ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            ["", $selfClass, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true],
            [__FILE__, "", ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [__FILE__, $selfClass, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, true, false],
            [self::TEST_FILE_TMP, $selfClass, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
            [__FILE__, "test", ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false],
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
            WorkerActionFactory::createFileHasInstantiableClass($filePath, $className)->isValid()
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
     * @param int $actionSeverity
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testCheckFileHasClass(
        string $filePath,
        string $className,
        int $actionSeverity,
        bool $expected,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals(
            $expected,
            WorkerActionFactory::createFileHasInstantiableClass($filePath, $className)
                ->setActionSeverity($actionSeverity)
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
            WorkerActionFactory::createFileHasInstantiableClass()
                ->hasClass($className)
                ->path($filePath)
        );
    }
}
