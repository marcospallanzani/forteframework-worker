<?php

namespace Tests\Unit\Transformers;

use Forte\Worker\Checkers\Checks\Files\FileExists;
use Forte\Worker\Transformers\ProjectTransformer;
use Forte\Worker\Transformers\Transforms\EmptyTransform;
use PHPUnit\Framework\TestCase;

/**
 * Class ProjectTransformerTest.
 *
 * @package Tests\Unit\Transformers
 */
class ProjectTransformerTest extends TestCase
{
    /**
     * Data provider for transformer tests.
     *
     * @return array
     */
    public function transformerProvider(): array
    {
        return [
            // dir path | number of failed transformations | transform instance | before check
            [__DIR__, 0, new EmptyTransform(), new FileExists(__FILE__)],
            [__DIR__, 0, null, null],
            [__DIR__, 1, new EmptyTransform(), new FileExists('wrong-file')],
        ];
    }

    /**
     * Test a successful call to the method ProjectTransformer::applyTransformations().
     *
     * @dataProvider transformerProvider
     *
     * @param string $dirPath
     * @param EmptyTransform $transform
     * @param FileExists $check
     * @param int $failedTransform
     */
    public function testProjectTransformerSuccess(
        string $dirPath,
        int $failedTransform,
        EmptyTransform $transform = null,
        FileExists $check = null
    ): void
    {
        $projectTransformer = new ProjectTransformer($dirPath);
        if (!is_null($transform) && !is_null($check)) {
            $projectTransformer->addTransform($transform->addBeforeAction($check));
        }
        $this->assertCount($failedTransform, $projectTransformer->applyTransformations());
    }
}
