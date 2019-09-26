<?php

namespace Forte\Worker\Tests\Unit\Builders;

use Forte\Stdlib\Exceptions\GeneralException;
use Forte\Stdlib\FileUtils;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Actions\Transforms\Files\ChangeConfigFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;
use Forte\Worker\Builders\ProjectRunnerBuilder;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ConfigurationException;
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
    const ENV_FILE_NAME = 'env.testModify';
    const JSON_FILE_NAME = 'test.json';
    const ENV_FILE_FULL_PATH = __DIR__ . '/' . self::ENV_FILE_NAME;
    const JSON_FILE_FULL_PATH = __DIR__ . '/' . self::JSON_FILE_NAME;
    const PHP_FILES_DIR_PATH = __DIR__ . '/phptests';
    const PHP_FILE_FULL_PATH = self::PHP_FILES_DIR_PATH . '/test.php';
    const PHP_CONTENT        = "<?php \n\nnamespace Test\Name\Space;\n\n/**\n *\n * @package \Test\Name\Space \n * Test\Name\n */ ";
    const JSON_CONTENT       = "{\"KEY1\":10,\"KEY2\":20,\"KEY3\":30}";
    const ENV_CONTENT        = "KEY1=10\nKEY2=20\nKEY3=30";

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        if (!is_dir(self::PHP_FILES_DIR_PATH)) {
            @mkdir(self::PHP_FILES_DIR_PATH);
        }
        @file_put_contents(self::PHP_FILE_FULL_PATH, self::PHP_CONTENT);
        @file_put_contents(self::JSON_FILE_FULL_PATH, self::JSON_CONTENT);
        @file_put_contents(self::ENV_FILE_FULL_PATH, self::ENV_CONTENT);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::ENV_FILE_FULL_PATH);
        @unlink(self::JSON_FILE_FULL_PATH);
        @unlink(self::PHP_FILE_FULL_PATH);
        @rmdir(self::PHP_FILES_DIR_PATH);
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
            ['hasInstantiableClass', [__FILE__, __CLASS__], FileHasInstantiableClass::class],
            ['modifyConfigValueByKey', [__FILE__, 'key', 'value'], ChangeConfigFileEntries::class],
            ['modifyConfigKey', [__FILE__, 'key', 'value'], ChangeConfigFileEntries::class],
            ['addConfigKey', [__FILE__, 'key', 'value'], ChangeConfigFileEntries::class],
            ['removeConfigKey', [__FILE__, 'key'], ChangeConfigFileEntries::class],
            ['addAction', [ActionFactory::createFileExists(__FILE__)], FileExists::class],
        ];
    }

    /**
     * Data provider for all namespaces tests.
     *
     * @return array
     */
    public function namespaceProvider(): array
    {
        return [
            [
                'Test\Name',
                'Modified\Name\Partial',
                true,
                "<?php \n\nnamespace Modified\Name\Partial\Space;\n\n/**\n *\n * @package \Modified\Name\Partial\Space \n * Test\Name\n */ "
            ],
            [
                'Test\Name',
                'Modified\Name\Partial',
                false,
                "<?php \n\nnamespace Modified\Name\Partial\Space;\n\n/**\n *\n * @package \Modified\Name\Partial\Space \n * Modified\Name\Partial\n */ "
            ],
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
        // We modify its content and check if it's correct
        $builder = new ProjectRunnerBuilder(__DIR__);
        $builder->modifyEnvFileConfigKey(self::ENV_FILE_NAME, "KEY1", 500);
        $builder->modifyEnvFileConfigKey(self::ENV_FILE_NAME, "KEY3", '');
        $builder->getRunner()->applyActions();

        // Now we open the file and check if the content has been correctly changed
        $envVariables = FileUtils::parseFile(self::ENV_FILE_FULL_PATH, FileUtils::CONTENT_TYPE_ENV);
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
     * Test function ProjectRunnerBuilder::modifyEnvFileConfigKey() with a non-existent file.
     *
     * @throws GeneralException
     */
    public function testModifyEnvFileConfigKeyWithWrongFile(): void
    {
        // We modify its content and check if it's correct
        $filePath = "xxx";
        $fileFullPath = __DIR__ . "/xxx";
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage("The file '$fileFullPath' does not exist.");
        $builder = new ProjectRunnerBuilder(__DIR__);
        $builder->modifyEnvFileConfigKey($filePath, "KEY1", 500);
        $builder->setFatalStatusForAllActions(true);
        $builder->getRunner()->applyActions();
    }

    /**
     * Test function ProjectRunnerBuilder::modifyConfigKey().
     *
     * @throws ActionException
     * @throws GeneralException
     */
    public function testModifyConfigKey(): void
    {
        // We modify its content and check if it's correct
        $builder = new ProjectRunnerBuilder(__DIR__);
        $builder->modifyConfigKey(self::JSON_FILE_NAME, "KEY1", "KEY10");
        $builder->getRunner()->applyActions();

        // Now we open the file and check if the content has been correctly changed
        $envVariables = FileUtils::parseFile(self::JSON_FILE_FULL_PATH, FileUtils::CONTENT_TYPE_JSON);
        $this->assertIsArray($envVariables);
        $this->assertCount(3, $envVariables);
        $this->assertArrayNotHasKey('KEY1', $envVariables);
        $this->assertArrayHasKey('KEY10', $envVariables);
        $this->assertArrayHasKey('KEY2', $envVariables);
        $this->assertArrayHasKey('KEY3', $envVariables);
        $this->assertEquals(10, $envVariables['KEY10']);
        $this->assertEquals(20, $envVariables['KEY2']);
        $this->assertEquals(30, $envVariables['KEY3']);
    }

    /**
     * Test function ProjectRunnerBuilder::changePhpNamespace().
     *
     * @dataProvider namespaceProvider
     *
     * @param string $oldNameSpace
     * @param string $newNameSpace
     * @param bool $isPartial
     * @param string $modifiedContent
     *
     * @throws ActionException
     * @throws ConfigurationException
     */
    public function testChangeNameSpace(
        string $oldNameSpace,
        string $newNameSpace,
        bool $isPartial,
        string $modifiedContent
    ): void
    {
        $builder = new ProjectRunnerBuilder(self::PHP_FILES_DIR_PATH);

        // Partial mode
        $builder->changeProjectPhpNamespace($oldNameSpace, $newNameSpace, $isPartial);
        $builder->getRunner()->applyActions();
        $this->assertEquals(
            $modifiedContent,
            @file_get_contents(self::PHP_FILE_FULL_PATH)
        );
    }
}
