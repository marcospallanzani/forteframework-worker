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

namespace Tests\Unit\Checkers\Checks\Files;

use Forte\Worker\Checkers\Checks\Files\FileDoesNotExist;
use Forte\Worker\Exceptions\CheckException;
use Forte\Worker\Exceptions\WorkerException;
use PHPUnit\Framework\TestCase;

/**
 * Class FileDoesNotExistTest.
 *
 * @package Tests\Unit\Checkers\Checks\Files
 */
class FileDoesNotExistTest extends TestCase
{
    /**
     * Data provider for does-not-exist tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            ["/xxx/xxx/eee/www/test.not.exist", true],
            [__FILE__, false]
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
     * Test method FileDoesNotExist::isValid().
     *
     * @dataProvider validationProvider
     *
     * @param string $filePath
     * @param bool $expected
     * @param bool $exceptionExpected
     *
     * @throws CheckException
     */
    public function testIsValid(string $filePath, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(CheckException::class);
        }
        $this->assertEquals($expected, (new FileDoesNotExist($filePath))->isValid());
    }

    /**
     * Test method FileDoesNotExist::run().
     *
     * @dataProvider filesProvider
     * @depends testIsValid
     *
     * @param string $filePath
     * @param bool $expected
     *
     * @throws WorkerException
     */
    public function testCheckFileDoesNotExist(string $filePath, bool $expected): void
    {
        $this->assertEquals($expected, (new FileDoesNotExist($filePath))->run());
    }

    /**
     * Test method FileDoesNotExist::stringify().
     */
    public function testStringify(): void
    {
        $filePath = "/path/to/test/file.php";
        $fileExists = new FileDoesNotExist($filePath);
        $this->assertEquals("Check if file '$filePath' does not exist.", (string) $fileExists);
        $this->assertEquals("Check if file '$filePath' does not exist.", $fileExists->stringify());
    }
}
