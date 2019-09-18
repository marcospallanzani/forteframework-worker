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

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class FileDoesNotExistTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Checks\Files
 */
class FileDoesNotExistTest extends BaseTest
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
     * @throws ValidationException
     */
    public function testIsValid(string $filePath, bool $expected, bool $exceptionExpected): void
    {
        if ($exceptionExpected) {
            $this->expectException(ValidationException::class);
        }
        $this->assertEquals($expected, ActionFactory::createFileDoesNotExist($filePath)->isValid());
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
        $this->assertEquals($expected, ActionFactory::createFileDoesNotExist($filePath)->run()->getResult());
    }

    /**
     * Test method FileDoesNotExist::stringify().
     */
    public function testStringify(): void
    {
        $filePath = "/path/to/test/file.php";
        $this->stringifyTest(
            "Check if file '$filePath' does not exist.",
            ActionFactory::createFileDoesNotExist($filePath)
        );
    }
}
