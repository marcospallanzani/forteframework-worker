<?php

namespace Tests\Unit\Helpers;

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\FileTrait;
use Tests\Unit\BaseTest;

/**
 * Class FileTraitTest
 *
 * @package Tests\Unit\Helpers
 */
class FileTraitTest extends BaseTest
{
    /**
     * Returns an anonymous class to test ClassAccessTrait.
     *
     * @return object
     */
    protected function getAnonymousTestClass()
    {
        return new class {
            use FileTrait;
        };
    }

    /**
     * Data provider for all files tests.
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            [__DIR__ . '/data/parsetest.ini', true, false, false],
            [__DIR__ . '/data/parsetest.json', true, false, false],
            [__DIR__ . '/data/parsetest.ini', true, true, false],
            [__DIR__ . '/data/parsetest.json', true, true, false],
            [__DIR__ . '/data/parsetest', false, false, false],
            [__DIR__ . '/data/parsetest', false, true, true],
        ];
    }

    /**
     * Tests the FileTrait::checkFileExists() function.
     *
     * @dataProvider filesProvider
     *
     * @param string $filePath
     * @param bool $expected
     * @param bool $raiseError
     * @param bool $exceptionExpected
     */
    public function testFileExists(string $filePath, bool $expected, bool $raiseError, bool $exceptionExpected): void
    {
        $class = $this->getAnonymousTestClass();
        if ($exceptionExpected) {
            $this->expectException(WorkerException::class);
        }
        $this->assertEquals($expected, $class->fileExists($filePath, $raiseError));
    }
}
