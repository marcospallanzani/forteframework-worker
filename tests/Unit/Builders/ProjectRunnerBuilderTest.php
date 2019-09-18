<?php

namespace Forte\Worker\Tests\Unit\Builders;

use Forte\Stdlib\Exceptions\GeneralException;
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
     * Test constants.
     */
    const ENV_FILE_PATH = __DIR__  . '/.env.testModify';

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::ENV_FILE_PATH);
    }

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

    /**
     * Test function ProjectRunnerBuilder::modifyEnvFileConfigKey().
     *
     * @throws GeneralException
     */
    public function testModifyEnvFileConfigKey(): void
    {
        // We write the test file first
        @file_put_contents(self::ENV_FILE_PATH, "KEY1=10\nKEY2=20\nKEY3=30");

        // We modify its content and check if it's correct
        $builder = new ProjectRunnerBuilder(__DIR__);
        $builder->modifyEnvFileConfigKey(self::ENV_FILE_PATH, "KEY1", 500);
        $builder->modifyEnvFileConfigKey(self::ENV_FILE_PATH, "KEY3", '');
        $builder->getRunner()->applyActions();

        // Now we open the file and check if the content has been correctly changed
        $envVariables = FileUtils::parseFile(self::ENV_FILE_PATH, FileUtils::CONTENT_TYPE_ENV);
        $this->assertIsArray($envVariables);
        $this->assertCount(3, $envVariables);
        $this->assertArrayHasKey('KEY1', $envVariables);
        $this->assertArrayHasKey('KEY2', $envVariables);
        $this->assertArrayHasKey('KEY3', $envVariables);
        $this->assertEquals(500, $envVariables['KEY1']);
        $this->assertEquals(20, $envVariables['KEY2']);
        $this->assertEquals('', $envVariables['KEY3']);
    }

    /**
     * Test function ProjectRunnerBuilder::modifyEnvFileConfigKey().
     *
     * @throws GeneralException
     */
    public function testModifyEnvFileConfigKeyWithErrors(): void
    {
        // We modify its content and check if it's correct
        $builder = new ProjectRunnerBuilder(__DIR__);
        $builder->modifyEnvFileConfigKey(__DIR__ . "/xxx", "KEY1", 500);
        $builder->getRunner()->applyActions();

//        // Now we open the file and check if the content has been correctly changed
//        $envVariables = FileUtils::parseFile(self::ENV_FILE_PATH, FileUtils::CONTENT_TYPE_ENV);
//        $this->assertIsArray($envVariables);
//        $this->assertCount(2, $envVariables);
//        $this->assertArrayHasKey('KEY1', $envVariables);
//        $this->assertArrayHasKey('KEY2', $envVariables);
//        $this->assertEquals(500, $envVariables['KEY1']);
//        $this->assertEquals(20, $envVariables['KEY2']);
    }
}
