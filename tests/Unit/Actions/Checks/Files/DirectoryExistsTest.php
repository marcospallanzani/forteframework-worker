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
 * Class DirectoryExistsTest.
 *
 * @package Tests\Unit\Actions\Checks\Files
 */
class DirectoryExistsTest extends BaseTest
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
     * Test method DirectoryExists::isValid().
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
        $this->assertEquals($expected, ActionFactory::createDirectoryExists($dirPath)->isValid());
    }

    /**
     * Test method DirectoryExists::stringify().
     */
    public function testStringify(): void
    {
        $dirPath = "/path/to/test";
        $this->stringifyTest(
            "Check if directory '$dirPath' exists.",
            ActionFactory::createDirectoryExists($dirPath)
        );
    }

    /**
     * Test method DirectoryExists::run().
     *
     * @depends testIsValid
     *
     * @throws WorkerException
     */
    public function testCheckDirectoryExists(): void
    {
        $dirPath = "/path/to/test";
        $this->assertEquals(false, ActionFactory::createDirectoryExists($dirPath)->run()->getResult());
        $this->assertEquals(false, ActionFactory::createDirectoryExists()->setPath($dirPath)->run()->getResult());
    }
}
