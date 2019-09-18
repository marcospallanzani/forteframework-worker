<?php

namespace Forte\Worker\Tests\Unit\Builders;

use Forte\Stdlib\FileUtils;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Actions\Transforms\Files\ChangeConfigFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;
use Forte\Worker\Builders\ProjectRunnerBuilder;
use Forte\Worker\Runners\ProjectRunner;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ProjectRunnerBuilderTest.
 *
 * @package Forte\Worker\Tests\Unit\Builders
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
            ['modifyConfigKey', [__FILE__, FileUtils::CONTENT_TYPE_JSON, 'key', 'value'], ChangeConfigFileEntries::class],
            ['addConfigKey', [__FILE__, FileUtils::CONTENT_TYPE_JSON, 'key', 'value'], ChangeConfigFileEntries::class],
            ['removeConfigKey', [__FILE__, FileUtils::CONTENT_TYPE_JSON, 'key'], ChangeConfigFileEntries::class],
            ['addAction', [ActionFactory::createFileExists(__FILE__)], FileExists::class],
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
