<?php

namespace Forte\Worker\Actions\Factories;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\DirectoryDoesNotExist;
use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use Forte\Worker\Actions\Checks\Files\FileDoesNotExist;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Actions\Checks\Files\ConfigFileHasValidEntries;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\Transforms\Arrays\ModifyArray;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Actions\Transforms\Files\ChangeConfigFileEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\ModifyFile;
use Forte\Worker\Actions\Transforms\Files\MoveDirectory;
use Forte\Worker\Actions\Transforms\Files\MoveFile;
use Forte\Worker\Actions\Transforms\Files\RemoveFile;
use Forte\Worker\Actions\Transforms\Files\RenameDirectory;
use Forte\Worker\Actions\Transforms\Files\RenameFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;

/**
 * Interface ActionFactoryInterface.
 *
 * @package Forte\Worker\Actions\Factories
 */
interface ActionFactoryInterface
{
    /**
     * Create an instance of the VerifyArray class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return VerifyArray An instance of VerifyArray.
     */
    public static function createVerifyArray(...$parameters): VerifyArray;

    /**
     * Create an instance of the DirectoryDoesNotExist class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return DirectoryDoesNotExist An instance of DirectoryDoesNotExist.
     */
    public static function createDirectoryDoesNotExist(...$parameters): DirectoryDoesNotExist;

    /**
     * Create an instance of the DirectoryExists class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return DirectoryExists An instance of DirectoryExists.
     */
    public static function createDirectoryExists(...$parameters): DirectoryExists;

    /**
     * Create an instance of the FileDoesNotExist class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileDoesNotExist An instance of FileDoesNotExist.
     */
    public static function createFileDoesNotExist(...$parameters): FileDoesNotExist;

    /**
     * Create an instance of the FileExists class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileExists An instance of FileExists.
     */
    public static function createFileExists(...$parameters): FileExists;

    /**
     * Create an instance of the FileHasInstantiableClass class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileHasInstantiableClass An instance of FileHasInstantiableClass.
     */
    public static function createFileHasInstantiableClass(...$parameters): FileHasInstantiableClass;

    /**
     * Create an instance of the ConfigFileHasValidEntries class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ConfigFileHasValidEntries An instance of ConfigFileHasValidEntries.
     */
    public static function createConfigFileHasValidEntries(...$parameters): ConfigFileHasValidEntries;

    /**
     * Create an instance of the VerifyString class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return VerifyString An instance of VerifyString.
     */
    public static function createVerifyString(...$parameters): VerifyString;

    /**
     * Create an instance of the ModifyArray class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ModifyArray An instance of ModifyArray.
     */
    public static function createModifyArray(...$parameters): ModifyArray;

    /**
     * Create an instance of the ChangeConfigFileEntries class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ChangeConfigFileEntries An instance of ChangeConfigFileEntries.
     */
    public static function createChangeConfigFileEntries(...$parameters): ChangeConfigFileEntries;

    /**
     * Create an instance of the CopyFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return CopyFile An instance of CopyFile.
     */
    public static function createCopyFile(...$parameters): CopyFile;

    /**
     * Create an instance of the ModifyFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ModifyFile An instance of ModifyFile.
     */
    public static function createModifyFile(...$parameters): ModifyFile;

    /**
     * Create an instance of the MoveDirectory class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return MoveDirectory An instance of MoveDirectory.
     */
    public static function createMoveDirectory(...$parameters): MoveDirectory;

    /**
     * Create an instance of the MoveFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return MoveFile An instance of MoveFile.
     */
    public static function createMoveFile(...$parameters): MoveFile;

    /**
     * Create an instance of the RemoveFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return RemoveFile An instance of RemoveFile.
     */
    public static function createRemoveFile(...$parameters): RemoveFile;

    /**
     * Create an instance of the RenameDirectory class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return RenameDirectory An instance of RenameDirectory.
     */
    public static function createRenameDirectory(...$parameters): RenameDirectory;

    /**
     * Create an instance of the RenameFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return RenameFile An instance of RenameFile.
     */
    public static function createRenameFile(...$parameters): RenameFile;

    /**
     * Create an instance of the UnzipFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return UnzipFile An instance of UnzipFile.
     */
    public static function createUnzipFile(...$parameters): UnzipFile;

    /**
     * Create an instance of the EmptyTransform class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return EmptyTransform An instance of EmptyTransform.
     */
    public static function createEmptyTransform(...$parameters): EmptyTransform;

    /**
     * Create an instance of the given Abstract subclass name (full namespace).
     *
     * @param string $class The AbstractAction subclass name to be created (full namespace).
     *
     * @return AbstractAction An instance of the required AbstractAction subclass.
     */
    public static function create(string $class): AbstractAction;
}
