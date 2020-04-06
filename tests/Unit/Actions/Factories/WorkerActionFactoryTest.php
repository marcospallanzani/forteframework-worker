<?php

namespace Forte\Worker\Tests\Unit\Actions\Factories;

use Forte\Stdlib\Exceptions\GeneralException;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\DirectoryDoesNotExist;
use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use Forte\Worker\Actions\Checks\Files\FileDoesNotExist;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Actions\Checks\Files\ConfigFileHasValidEntries;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\Conditionals\ForEachLoop;
use Forte\Worker\Actions\Conditionals\IfStatement;
use Forte\Worker\Actions\Conditionals\SwitchStatement;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Actions\Lists\FilesInDirectory;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;
use Forte\Worker\Actions\Transforms\Files\ChangeConfigFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\MakeDirectory;
use Forte\Worker\Actions\Transforms\Files\ModifyFile;
use Forte\Worker\Actions\Transforms\Files\ModifyFileContent;
use Forte\Worker\Actions\Transforms\Files\MoveDirectory;
use Forte\Worker\Actions\Transforms\Files\MoveFile;
use Forte\Worker\Actions\Transforms\Files\RemoveFile;
use Forte\Worker\Actions\Transforms\Files\RenameDirectory;
use Forte\Worker\Actions\Transforms\Files\RenameFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class WorkerActionFactoryTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Factories
 */
class WorkerActionFactoryTest extends BaseTest
{
    /**
     * Data provider for all class generation tests.
     *
     * @return array
     */
    public function classesProvider(): array
    {
        $wrongParams = [new \stdClass(), true, null];

        return [
            [VerifyArray::class, $wrongParams],
            [DirectoryDoesNotExist::class, $wrongParams],
            [DirectoryExists::class, $wrongParams],
            [FileDoesNotExist::class, $wrongParams],
            [FileExists::class, $wrongParams],
            [FileHasInstantiableClass::class, $wrongParams],
            [ConfigFileHasValidEntries::class, $wrongParams],
            [VerifyString::class, $wrongParams],
            [ModifyArray::class, $wrongParams],
            [ChangeConfigFileEntries::class, $wrongParams],
            [CopyFile::class, $wrongParams],
            [ModifyFile::class, $wrongParams],
            [ModifyFileContent::class, $wrongParams],
            [MoveDirectory::class, $wrongParams],
            [MoveFile::class, $wrongParams],
            [RemoveFile::class, $wrongParams],
            [RenameDirectory::class, $wrongParams],
            [RenameFile::class, $wrongParams],
            [UnzipFile::class, $wrongParams],
            [MakeDirectory::class, $wrongParams],
            [IfStatement::class, $wrongParams],
            [ForEachLoop::class, $wrongParams],
            [FilesInDirectory::class, $wrongParams],
        ];
    }

    /**
     * Test for all ActionFactory creation methods.
     *
     * @throws WorkerException
     * @throws GeneralException
     * @throws ConfigurationException
     */
    public function testCreateMethods(): void
    {
        $this->assertInstanceOf(VerifyArray::class, WorkerActionFactory::createVerifyArray());
        $this->assertInstanceOf(VerifyArray::class, WorkerActionFactory::create(VerifyArray::class));

        $this->assertInstanceOf(DirectoryDoesNotExist::class, WorkerActionFactory::createDirectoryDoesNotExist());
        $this->assertInstanceOf(DirectoryDoesNotExist::class, WorkerActionFactory::create(DirectoryDoesNotExist::class));

        $this->assertInstanceOf(DirectoryExists::class, WorkerActionFactory::createDirectoryExists());
        $this->assertInstanceOf(DirectoryExists::class, WorkerActionFactory::create(DirectoryExists::class));

        $this->assertInstanceOf(FileDoesNotExist::class, WorkerActionFactory::createFileDoesNotExist());
        $this->assertInstanceOf(FileDoesNotExist::class, WorkerActionFactory::create(FileDoesNotExist::class));

        $this->assertInstanceOf(FileExists::class, WorkerActionFactory::createFileExists());
        $this->assertInstanceOf(FileExists::class, WorkerActionFactory::create(FileExists::class));

        $this->assertInstanceOf(FileHasInstantiableClass::class, WorkerActionFactory::createFileHasInstantiableClass());
        $this->assertInstanceOf(FileHasInstantiableClass::class, WorkerActionFactory::create(FileHasInstantiableClass::class));

        $this->assertInstanceOf(ConfigFileHasValidEntries::class, WorkerActionFactory::createConfigFileHasValidEntries());
        $this->assertInstanceOf(ConfigFileHasValidEntries::class, WorkerActionFactory::create(ConfigFileHasValidEntries::class));

        $this->assertInstanceOf(VerifyString::class, WorkerActionFactory::createVerifyString());
        $this->assertInstanceOf(VerifyString::class, WorkerActionFactory::create(VerifyString::class));

        $this->assertInstanceOf(ModifyArray::class, WorkerActionFactory::createModifyArray());
        $this->assertInstanceOf(ModifyArray::class, WorkerActionFactory::create(ModifyArray::class));

        $this->assertInstanceOf(ChangeConfigFileEntries::class, WorkerActionFactory::createChangeConfigFileEntries());
        $this->assertInstanceOf(ChangeConfigFileEntries::class, WorkerActionFactory::create(ChangeConfigFileEntries::class));

        $this->assertInstanceOf(CopyFile::class, WorkerActionFactory::createCopyFile());
        $this->assertInstanceOf(CopyFile::class, WorkerActionFactory::create(CopyFile::class));

        $this->assertInstanceOf(ModifyFile::class, WorkerActionFactory::createModifyFile());
        $this->assertInstanceOf(ModifyFile::class, WorkerActionFactory::create(ModifyFile::class));

        $this->assertInstanceOf(ModifyFileContent::class, WorkerActionFactory::createModifyFileContent());
        $this->assertInstanceOf(ModifyFileContent::class, WorkerActionFactory::create(ModifyFileContent::class));

        $this->assertInstanceOf(MoveDirectory::class, WorkerActionFactory::createMoveDirectory());
        $this->assertInstanceOf(MoveDirectory::class, WorkerActionFactory::create(MoveDirectory::class));

        $this->assertInstanceOf(MoveFile::class, WorkerActionFactory::createMoveFile());
        $this->assertInstanceOf(MoveFile::class, WorkerActionFactory::create(MoveFile::class));

        $this->assertInstanceOf(RemoveFile::class, WorkerActionFactory::createRemoveFile());
        $this->assertInstanceOf(RemoveFile::class, WorkerActionFactory::create(RemoveFile::class));

        $this->assertInstanceOf(RenameDirectory::class, WorkerActionFactory::createRenameDirectory());
        $this->assertInstanceOf(RenameDirectory::class, WorkerActionFactory::create(RenameDirectory::class));

        $this->assertInstanceOf(RenameFile::class, WorkerActionFactory::createRenameFile());
        $this->assertInstanceOf(RenameFile::class, WorkerActionFactory::create(RenameFile::class));

        $this->assertInstanceOf(UnzipFile::class, WorkerActionFactory::createUnzipFile());
        $this->assertInstanceOf(UnzipFile::class, WorkerActionFactory::create(UnzipFile::class));

        $this->assertInstanceOf(MakeDirectory::class, WorkerActionFactory::createMakeDirectory());
        $this->assertInstanceOf(MakeDirectory::class, WorkerActionFactory::create(MakeDirectory::class));

        $this->assertInstanceOf(IfStatement::class, WorkerActionFactory::createIfStatement());
        $this->assertInstanceOf(IfStatement::class, WorkerActionFactory::create(IfStatement::class));

        $this->assertInstanceOf(ForEachLoop::class, WorkerActionFactory::createForEachLoop());
        $this->assertInstanceOf(ForEachLoop::class, WorkerActionFactory::create(ForEachLoop::class));

        $this->assertInstanceOf(SwitchStatement::class, WorkerActionFactory::createSwitchStatement(true, null));
        $this->assertInstanceOf(SwitchStatement::class, WorkerActionFactory::create(SwitchStatement::class, true, null));

        $this->assertInstanceOf(FilesInDirectory::class, WorkerActionFactory::createFilesInDirectory());
        $this->assertInstanceOf(FilesInDirectory::class, WorkerActionFactory::create(FilesInDirectory::class));
    }

    /**
     * Test ActionFactory::create() method with a non-supported class.
     *
     * @throws WorkerException
     */
    public function testFailure(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage("The given action type '".self::class."' is not supported.");
        WorkerActionFactory::create(self::class);
    }

    /**
     * Test ActionFactory::create() method with wrong parameters (wrong parameter types).
     *
     * @dataProvider classesProvider
     *
     * @param string $className
     * @param array $wrongParameters
     *
     * @throws WorkerException
     */
    public function testWrongArguments(string $className, array $wrongParameters): void
    {
        $this->expectException(WorkerException::class);
        WorkerActionFactory::create($className, ...$wrongParameters);
    }
}
