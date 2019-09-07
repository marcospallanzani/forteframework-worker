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

use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use Forte\Worker\Exceptions\ActionException;
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
     * @throws ActionException
     */
    public function testIsValid(string $dirPath, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }
        $this->assertEquals($expected, (new DirectoryExists($dirPath))->isValid());
    }

    /**
     * Test method DirectoryExists::stringify().
     */
    public function testStringify(): void
    {
        $directoryPath = "/path/to/test";
        $directoryExists = new DirectoryExists($directoryPath);
        $this->assertEquals("Check if directory '$directoryPath' exists.", (string) $directoryExists);
        $this->assertEquals("Check if directory '$directoryPath' exists.", $directoryExists->stringify());
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
        $directoryPath = "/path/to/test";
        $this->assertEquals(false, (new DirectoryExists($directoryPath))->run()->getResult());
        $this->assertEquals(false, (new DirectoryExists())->setPath($directoryPath)->run()->getResult());
    }
}
