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
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Tests\Unit\BaseTest;

/**
 * Class DirectoryDoesNotExistTest.
 *
 * @package Tests\Unit\Actions\Checks\Files
 */
class DirectoryDoesNotExistTest extends BaseTest
{
    /**
     * Data provider for isValid() tests.
     *
     * @return array
     */
    public function validationProvider(): array
    {
        return [
            ["", false, true],
            [__DIR__, true, false]
        ];
    }

    /**
     * Test method DirectoryDoesNotExist::isValid().
     *
     * @dataProvider validationProvider
     *
     * @param string $dirPath
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws ValidationException
     */
    public function testIsValid(string $dirPath, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(ValidationException::class);
        }
        $this->assertEquals($expected, ActionFactory::createDirectoryDoesNotExist($dirPath)->isValid());
    }

    /**
     * Test method DirectoryDoesNotExist::run().
     *
     * @depends testIsValid
     *
     * @throws WorkerException
     */
    public function testCheckDirectoryExists(): void
    {
        $directoryPath = "/path/to/test";
        $this->assertEquals(true, ActionFactory::createDirectoryDoesNotExist($directoryPath)->run()->getResult());
        $this->assertEquals(true, ActionFactory::createDirectoryDoesNotExist()->setPath($directoryPath)->run()->getResult());
    }

    /**
     * Test method DirectoryDoesNotExist::stringify().
     */
    public function testStringify(): void
    {
        $directoryPath = "/path/to/test/file.php";
        $this->stringifyTest(
            "Check if directory '$directoryPath' does not exist.",
            ActionFactory::createDirectoryDoesNotExist($directoryPath)
        );
    }
}
