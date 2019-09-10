<?php

namespace Tests\Unit\Actions\Factories;

use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\DirectoryDoesNotExist;
use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use Forte\Worker\Actions\Checks\Files\FileDoesNotExist;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Actions\Checks\Files\FileHasValidEntries;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Actions\Transforms\Files\ChangeFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
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

        $this->assertInstanceOf(FileHasValidEntries::class, ActionFactory::createFileHasValidEntries());
        $this->assertInstanceOf(FileHasValidEntries::class, ActionFactory::create(FileHasValidEntries::class));

        $this->assertInstanceOf(VerifyString::class, ActionFactory::createVerifyString());
        $this->assertInstanceOf(VerifyString::class, ActionFactory::create(VerifyString::class));

        $this->assertInstanceOf(ModifyArray::class, ActionFactory::createModifyArray());
        $this->assertInstanceOf(ModifyArray::class, ActionFactory::create(ModifyArray::class));

        $this->assertInstanceOf(EmptyTransform::class, ActionFactory::createEmptyTransform());
        $this->assertInstanceOf(EmptyTransform::class, ActionFactory::create(EmptyTransform::class));

        $this->assertInstanceOf(ChangeFileEntries::class, ActionFactory::createChangeFileEntries());
        $this->assertInstanceOf(ChangeFileEntries::class, ActionFactory::create(ChangeFileEntries::class));

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
    }
}