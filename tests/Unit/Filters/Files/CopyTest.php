<?php

namespace Tests\Unit\Filters\Files;

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Filters\Files\Copy as CopyFilter;
use PHPUnit\Framework\TestCase;

/**
 * Class CopyTest
 *
 * @package Tests\Unit\Filters\Files
 */
class CopyTest extends TestCase
{
    /**
     * Temporary files constants
     */
    const TEST_FILE_TMP      = __DIR__ . '/file-tests';
    const TEST_FILE_TMP_COPY = __DIR__ . '/file-tests_COPY';
    const TEST_CONTENT       = "ANY CONTENT";
    const TEST_WRONG_FILE    = "/path/to/non/existent/file.php";

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        @file_put_contents(self::TEST_FILE_TMP, self::TEST_CONTENT);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_FILE_TMP);
        @unlink(self::TEST_FILE_TMP_COPY);
    }

    /**
     * Test the method Forte\Worker\Filters\Files\Copy::filter().
     *
     * @throws WorkerException
     */
    public function testFilter(): void
    {
        $copyFilter = new CopyFilter([
            'target' => self::TEST_FILE_TMP_COPY,
            'overwrite' => true
        ]);
        $copyFilter->filter(self::TEST_FILE_TMP);
        $this->assertEquals(self::TEST_CONTENT, file_get_contents(self::TEST_FILE_TMP_COPY));
    }

    /**
     * Test the method Forte\Worker\Filters\Files\Copy::filter() on failure.
     *
     * @throws WorkerException
     */
    public function testFilterFail(): void
    {
        $copyFilter = new CopyFilter([
            'target' => self::TEST_FILE_TMP_COPY,
            'overwrite' => true
        ]);
        $copyFilter->filter(self::TEST_WRONG_FILE);
        $this->assertEquals(false, @file_get_contents(self::TEST_FILE_TMP_COPY));
    }

    /**
     * Test the method Forte\Worker\Filters\Files\Copy::filter() on failure.
     *
     * @throws WorkerException
     */
    public function testFilterScalarFail(): void
    {
        $copyFilter = new CopyFilter([
            'target' => self::TEST_FILE_TMP_COPY,
            'overwrite' => true
        ]);
        $this->assertEquals(null, $copyFilter->filter(null));
    }

    /**
     * Test the method Forte\Worker\Filters\Files\Copy::filter() on runtime failure.
     */
    public function testFilterRuntimeFail(): void
    {
        $this->expectException(WorkerException::class);
        $copyFilterMock = \Mockery::mock(CopyFilter::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $copyFilterMock
            ->shouldReceive('getNewName')
            ->once()
            ->andReturn([
                'target' => self::TEST_FILE_TMP_COPY,
                'source' => self::TEST_FILE_TMP_COPY
            ])
            ->shouldReceive('copyFileToDestination')
            ->once()
            ->andReturn(false)
        ;
        $copyFilterMock->filter(self::TEST_WRONG_FILE);
    }
}
