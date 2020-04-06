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

use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class FileExistsTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Checks\Files
 */
class FileExistsTest extends BaseTest
{
    /**
     * Data provider for file-exists tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            ["/xxx/xxx/eee/www/test.not.exist", false],
            [__FILE__, true]
        ];
    }

    /**
     * Data provider for isValid() tests.
     *
     * @return array
     */
    public function validationProvider(): array
    {
        return [
            ["", false, true],
            [__FILE__, true, false]
        ];
    }

    /**
     * Test method FileExists::isValid().
     *
     * @dataProvider validationProvider
     *
     * @param string $filePath
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ValidationException
     */
    public function testIsValid(string $filePath, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(ValidationException::class);
        }
        $this->assertEquals($expected, WorkerActionFactory::createFileExists($filePath)->isValid());
    }

    /**
     * Test method FileExists::run().
     *
     * @dataProvider filesProvider
     * @depends testIsValid
     *
     * @param string $filePath
     * @param bool $expected
     *
     * @throws WorkerException
     */
    public function testCheckFileExists(string $filePath, bool $expected): void
    {
        $this->assertEquals($expected, WorkerActionFactory::createFileExists($filePath)->run()->getResult());
        $this->assertEquals($expected, WorkerActionFactory::createFileExists()->path($filePath)->run()->getResult());
    }

    /**
     * Test method FileExists::stringify().
     */
    public function testStringify(): void
    {
        $filePath = "/path/to/test/file.php";
        $this->stringifyTest(
            "Check if file '$filePath' exists.",
            WorkerActionFactory::createFileExists($filePath)
        );
    }
}
