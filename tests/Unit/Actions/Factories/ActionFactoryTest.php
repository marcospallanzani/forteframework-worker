<?php

namespace Tests\Unit\Actions\Factories;

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
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Actions\Transforms\Files\ChangeConfigFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\MakeDirectory;
use Forte\Worker\Actions\Transforms\Files\ModifyFile;
use Forte\Worker\Actions\Transforms\Files\MoveDirectory;
use Forte\Worker\Actions\Transforms\Files\MoveFile;
use Forte\Worker\Actions\Transforms\Files\RemoveFile;
use Forte\Worker\Actions\Transforms\Files\RenameDirectory;
use Forte\Worker\Actions\Transforms\Files\RenameFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;
use Forte\Worker\Exceptions\WorkerException;
use Tests\Unit\BaseTest;

/**
 * Class ActionFactoryTest.
 *
 * @package Tests\Unit\Actions\Factories
 */
class ActionFactoryTest extends BaseTest
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
            [MoveDirectory::class, $wrongParams],
            [MoveFile::class, $wrongParams],
            [RemoveFile::class, $wrongParams],
            [RenameDirectory::class, $wrongParams],
            [RenameFile::class, $wrongParams],
            [UnzipFile::class, $wrongParams],
            [MakeDirectory::class, $wrongParams],
            [IfStatement::class, $wrongParams],
            [ForEachLoop::class, $wrongParams],
        ];
    }

    /**
     * Test for all ActionFactory creation methods.
     *
     * @throws WorkerException
     */
    public function testCreateMethods(): void
    {
        $this->assertInstanceOf(VerifyArray::class, ActionFactory::createVerifyArray());
        $this->assertInstanceOf(VerifyArray::class, ActionFactory::create(VerifyArray::class));

        $this->assertInstanceOf(DirectoryDoesNotExist::class, ActionFactory::createDirectoryDoesNotExist());
        $this->assertInstanceOf(DirectoryDoesNotExist::class, ActionFactory::create(DirectoryDoesNotExist::class));

        $this->assertInstanceOf(DirectoryExists::class, ActionFactory::createDirectoryExists());
        $this->assertInstanceOf(DirectoryExists::class, ActionFactory::create(DirectoryExists::class));

        $this->assertInstanceOf(FileDoesNotExist::class, ActionFactory::createFileDoesNotExist());
        $this->assertInstanceOf(FileDoesNotExist::class, ActionFactory::create(FileDoesNotExist::class));

        $this->assertInstanceOf(FileExists::class, ActionFactory::createFileExists());
        $this->assertInstanceOf(FileExists::class, ActionFactory::create(FileExists::class));

        $this->assertInstanceOf(FileHasInstantiableClass::class, ActionFactory::createFileHasInstantiableClass());
        $this->assertInstanceOf(FileHasInstantiableClass::class, ActionFactory::create(FileHasInstantiableClass::class));

        $this->assertInstanceOf(ConfigFileHasValidEntries::class, ActionFactory::createConfigFileHasValidEntries());
        $this->assertInstanceOf(ConfigFileHasValidEntries::class, ActionFactory::create(ConfigFileHasValidEntries::class));

        $this->assertInstanceOf(VerifyString::class, ActionFactory::createVerifyString());
        $this->assertInstanceOf(VerifyString::class, ActionFactory::create(VerifyString::class));

        $this->assertInstanceOf(ModifyArray::class, ActionFactory::createModifyArray());
        $this->assertInstanceOf(ModifyArray::class, ActionFactory::create(ModifyArray::class));

        $this->assertInstanceOf(EmptyTransform::class, ActionFactory::createEmptyTransform());
        $this->assertInstanceOf(EmptyTransform::class, ActionFactory::create(EmptyTransform::class));

        $this->assertInstanceOf(ChangeConfigFileEntries::class, ActionFactory::createChangeConfigFileEntries());
        $this->assertInstanceOf(ChangeConfigFileEntries::class, ActionFactory::create(ChangeConfigFileEntries::class));

        $this->assertInstanceOf(CopyFile::class, ActionFactory::createCopyFile());
        $this->assertInstanceOf(CopyFile::class, ActionFactory::create(CopyFile::class));

        $this->assertInstanceOf(ModifyFile::class, ActionFactory::createModifyFile());
        $this->assertInstanceOf(ModifyFile::class, ActionFactory::create(ModifyFile::class));

        $this->assertInstanceOf(MoveDirectory::class, ActionFactory::createMoveDirectory());
        $this->assertInstanceOf(MoveDirectory::class, ActionFactory::create(MoveDirectory::class));

        $this->assertInstanceOf(MoveFile::class, ActionFactory::createMoveFile());
        $this->assertInstanceOf(MoveFile::class, ActionFactory::create(MoveFile::class));

        $this->assertInstanceOf(RemoveFile::class, ActionFactory::createRemoveFile());
        $this->assertInstanceOf(RemoveFile::class, ActionFactory::create(RemoveFile::class));

        $this->assertInstanceOf(RenameDirectory::class, ActionFactory::createRenameDirectory());
        $this->assertInstanceOf(RenameDirectory::class, ActionFactory::create(RenameDirectory::class));

        $this->assertInstanceOf(RenameFile::class, ActionFactory::createRenameFile());
        $this->assertInstanceOf(RenameFile::class, ActionFactory::create(RenameFile::class));

        $this->assertInstanceOf(UnzipFile::class, ActionFactory::createUnzipFile());
        $this->assertInstanceOf(UnzipFile::class, ActionFactory::create(UnzipFile::class));

        $this->assertInstanceOf(MakeDirectory::class, ActionFactory::createMakeDirectory());
        $this->assertInstanceOf(MakeDirectory::class, ActionFactory::create(MakeDirectory::class));

        $this->assertInstanceOf(IfStatement::class, ActionFactory::createIfStatement());
        $this->assertInstanceOf(IfStatement::class, ActionFactory::create(IfStatement::class));

        $this->assertInstanceOf(ForEachLoop::class, ActionFactory::createForEachLoop());
        $this->assertInstanceOf(ForEachLoop::class, ActionFactory::create(ForEachLoop::class));

        $this->assertInstanceOf(SwitchStatement::class, ActionFactory::createSwitchStatement(true, null));
        $this->assertInstanceOf(SwitchStatement::class, ActionFactory::create(SwitchStatement::class, true, null));
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
        ActionFactory::create(self::class);
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
        ActionFactory::create($className, ...$wrongParameters);
    }
}
