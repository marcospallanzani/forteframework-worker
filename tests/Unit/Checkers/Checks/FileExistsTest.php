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

namespace Tests\Unit\Checkers\Checks;

use Forte\Api\Generator\Checkers\Checks\FileExists;
use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Node\File;

/**
 * Class FileExistsTest.
 *
 * @package Tests\Unit\Checkers\Checks
 */
class FileExistsTest extends TestCase
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
     * @throws CheckException
     */
    public function testIsValid(string $filePath, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(CheckException::class);
        }
        $this->assertEquals($expected, (new FileExists($filePath))->isValid());
    }

    /**
     * Test method FileExists::run().
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $expected
     *
     * @throws GeneratorException
     */
    public function testCheckFileExists(string $filePath, bool $expected): void
    {
        $this->assertEquals($expected, (new FileExists($filePath))->run());
    }

    /**
     * Test method FileExists::stringify().
     */
    public function testStringify(): void
    {
        $filePath = "/path/to/test/file.php";
        $fileExists = new FileExists($filePath);
        $this->assertEquals("Check if file '$filePath' exists.", (string) $fileExists);
        $this->assertEquals("Check if file '$filePath' exists.", $fileExists->stringify());
    }
}