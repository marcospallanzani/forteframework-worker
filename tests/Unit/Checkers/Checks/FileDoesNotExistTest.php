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

use Forte\Api\Generator\Checkers\Checks\FileDoesNotExist;
use PHPUnit\Framework\TestCase;

/**
 * Class FileDoesNotExistTest.
 *
 * @package Tests\Unit\Checkers\Checks
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
     * Test method FileDoesNotExist::run().
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $expected
     *
     * @throws \Forte\Api\Generator\Exceptions\GeneratorException
     */
    public function testCheckFileDoesNotExist(string $filePath, bool $expected): void
    {
        $this->assertEquals((new FileDoesNotExist($filePath))->run(), $expected);
    }
}