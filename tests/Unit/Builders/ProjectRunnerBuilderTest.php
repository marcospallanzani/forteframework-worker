<?php

namespace Tests\Unit\Builders;

use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Actions\Transforms\Files\ChangeFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;
use Forte\Worker\Builders\ProjectRunnerBuilder;
use Forte\Worker\Helpers\FileParser;
use Forte\Worker\Runners\ProjectRunner;
use Tests\Unit\BaseTest;

/**
 * Class ProjectRunnerBuilderTest.
 *
 * @package Tests\Unit\Builders
 */
class ProjectRunnerBuilderTest extends BaseTest
{
    /**
     * Data provider for all add-action tests.
     *
     * @return array
     */
    public function addActionProvider(): array
    {
        return [
            // Add method | add method params | expected action class
            ['unzipFile', ['test.zip'], UnzipFile::class],
            ['copyFileTo', [__FILE__], CopyFile::class],
            ['hasInstantiableClass', [__FILE__, __CLASS__], EmptyTransform::class],
            ['modifyConfigKey', [__FILE__, FileParser::CONTENT_TYPE_JSON, 'key', 'value'], ChangeFileEntries::class],
            ['addConfigKey', [__FILE__, FileParser::CONTENT_TYPE_JSON, 'key', 'value'], ChangeFileEntries::class],
            ['removeConfigKey', [__FILE__, FileParser::CONTENT_TYPE_JSON, 'key'], ChangeFileEntries::class],
            ['addAction', [new FileExists(__FILE__)], FileExists::class],
        ];
    }

    /**
     * Test the ProjectRunnerBuilder construction methods.
     */
    public function testInit(): void
    {
        $builder = new ProjectRunnerBuilder(__DIR__);
        $this->assertEquals(__FILE__, $builder->getFilePathInProject('ProjectRunnerBuilderTest.php'));
        $this->assertInstanceOf(ProjectRunner::class, $builder->getRunner());
    }

    /**
     * Test the ProjectRunnerBuilder add-actions methods.
     *
     * @dataProvider addActionProvider
     *
     * @param string $initMethod
     * @param array $params
     * @param string $expectedActionClass
     */
    public function testAddActionMethods(string $initMethod, array $params, string $expectedActionClass): void
    {
        $builder = new ProjectRunnerBuilder(__DIR__);
        $builder->$initMethod(...$params);
        $this->assertInstanceOf(ProjectRunner::class, $builder->getRunner());
        $this->assertCount(1, $builder->getRunner()->getActions());
        $action = current($builder->getRunner()->getActions());
        $this->assertInstanceOf($expectedActionClass, $action);
    }
}
