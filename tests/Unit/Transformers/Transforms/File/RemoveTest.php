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

namespace Tests\Unit\Transformers\Transforms\File;

use Forte\Api\Generator\Checkers\Checks\FileDoesNotExist;
use Forte\Api\Generator\Checkers\Checks\FileExists;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Transformers\Transforms\File\Remove;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveTest.
 *
 * @package Tests\Unit\Transformers\Transforms\File
 */
class RemoveTest extends TestCase
{
    const TEST_FILE_TMP   = __DIR__ . '/files/file-tests-template';
    const TEST_DIR_TMP    = __DIR__ . '/files/todelete';
    const TEST_FILE_TXT   = self::TEST_DIR_TMP . '/file-tests-template.txt';
    const TEST_FILE_JSON  = self::TEST_DIR_TMP . '/file-tests-template.json';
    const TEST_WRONG_FILE = "/path/to/non/existent/file.php";

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        // We have to copy the template file, which will be deleted by this test
        if (!is_dir(self::TEST_DIR_TMP)) {
            mkdir(self::TEST_DIR_TMP);
        }
        copy(self::TEST_FILE_TMP, self::TEST_FILE_TXT);
        copy(self::TEST_FILE_TMP, self::TEST_FILE_JSON);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_FILE_TXT);
        @unlink(self::TEST_FILE_JSON);
        @rmdir(self::TEST_DIR_TMP);
    }

    /**
     * Data provider for all files tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            [self::TEST_FILE_TXT, true],
            [self::TEST_FILE_JSON, true],
            [self::TEST_WRONG_FILE, false]
        ];
    }

    /**
     * Test the Forte\Api\Generator\Transformers\Transforms\File\Remove::run() method
     * for one single file.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $expected
     *
     * @throws GeneratorException
     */
    public function testRemoveFile(string $filePath, bool $expected): void
    {
        if (!$expected) {
            $this->expectException(GeneratorException::class);
        }
        $this->assertEquals(
            $expected,
            (new Remove())
                ->removeFile($filePath)
                ->addBeforeCheck(new FileExists($filePath))
                ->addAfterCheck(new FileDoesNotExist($filePath))
                ->run()
        );
    }

    /**
     * Test the Forte\Api\Generator\Transformers\Transforms\File\Remove::run() method
     * for one single file without the pre- and post-transform checks.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $expected
     *
     * @throws GeneratorException
     */
    public function testRemoveFileNoChecks(string $filePath, bool $expected): void
    {
        if (!$expected) {
            $this->expectException(TransformException::class);
        }
        $this->assertEquals($expected, (new Remove())->removeFile($filePath)->run());
    }

    /**
     * Test the Forte\Api\Generator\Transformers\Transforms\File\Remove::run() method
     * in mode "single-file", but with a folder as a parameter.
     *
     * @throws GeneratorException
     */
    public function testRemoveFileDirectoryException(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $this->expectException(TransformException::class);
        (new Remove())->removeFile(self::TEST_DIR_TMP)->run();
    }

    /**
     * Test the Forte\Api\Generator\Transformers\Transforms\File\Remove::run() method
     * in mode "directory-file", with a valid folder parameter.
     *
     * @throws GeneratorException
     */
    public function testRemoveDirectory(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $removeTransform = (new Remove())
            ->removeDirectory(self::TEST_DIR_TMP)
//TODO ADD BEFORE AND AFTER CHECKS
            ->addBeforeCheck(new FileExists(self::TEST_DIR_TMP))
            ->addAfterCheck(new FileDoesNotExist(self::TEST_DIR_TMP))
            ->run();

        $this->assertTrue($removeTransform);
    }

    /**
     * Test the Forte\Api\Generator\Transformers\Transforms\File\Remove::run() method
     * in mode "directory-file", with a valid folder parameter.
     *
     * @throws GeneratorException
     */
    public function testRemoveDirectoryWithFilePatter(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $removeTransform = (new Remove())
            ->removeFilePattern(self::TEST_DIR_TMP . '/*json')
            ->addBeforeCheck(new FileExists(self::TEST_FILE_JSON))
            ->addAfterCheck(new FileDoesNotExist(self::TEST_FILE_JSON))
            ->run();

        $this->assertTrue($removeTransform);
    }

    /**
     * Test the Forte\Api\Generator\Transformers\Transforms\File\Remove::run() method
     * in mode "directory-file", but with a wrong or non-existing folder as a parameter.
     *
     * @throws GeneratorException
     */
    public function testRemoveDirectoryExpectException(): void
    {
        // If we try to call the removeFile() method for a directory,
        // an exception should be thrown.
        $this->expectException(TransformException::class);
        (new Remove())->removeFile(__DIR__ . '/files/xxx/')->run();
    }
}