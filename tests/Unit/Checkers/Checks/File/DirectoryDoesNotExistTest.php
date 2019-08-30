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

namespace Tests\Unit\Checkers\Checks\File;

use Forte\Api\Generator\Checkers\Checks\File\DirectoryDoesNotExist;
use PHPUnit\Framework\TestCase;

/**
 * Class DirectoryDoesNotExistTest.
 *
 * @package Tests\Unit\Checkers\Checks\File
 */
class DirectoryDoesNotExistTest extends TestCase
{
    /**
     * Test method DirectoryDoesNotExist::stringify().
     */
    public function testStringify(): void
    {
        $directoryPath = "/path/to/test/file.php";
        $directoryDoesNotExist = new DirectoryDoesNotExist($directoryPath);
        $this->assertEquals("Check if directory '$directoryPath' does not exist.", (string) $directoryDoesNotExist);
        $this->assertEquals("Check if directory '$directoryPath' does not exist.", $directoryDoesNotExist->stringify());
    }
}