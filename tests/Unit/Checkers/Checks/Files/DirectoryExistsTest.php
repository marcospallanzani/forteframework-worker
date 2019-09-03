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

use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use PHPUnit\Framework\TestCase;

/**
 * Class DirectoryExistsTest.
 *
 * @package Tests\Unit\Checkers\Checks\Files
 */
class DirectoryExistsTest extends TestCase
{
    /**
     * Test method DirectoryExists::stringify().
     */
    public function testStringify(): void
    {
        $directoryPath = "/path/to/test/file.php";
        $directoryExists = new DirectoryExists($directoryPath);
        $this->assertEquals("Check if directory '$directoryPath' exists.", (string) $directoryExists);
        $this->assertEquals("Check if directory '$directoryPath' exists.", $directoryExists->stringify());
    }
}
