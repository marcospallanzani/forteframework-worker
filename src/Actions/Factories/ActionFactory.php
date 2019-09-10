<?php

namespace Forte\Worker\Actions\Factories;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\DirectoryDoesNotExist;
use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use Forte\Worker\Actions\Checks\Files\FileDoesNotExist;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Actions\Checks\Files\FileHasValidEntries;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
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

/**
 * Class ActionFactory. A basic ActionFactoryInterface implementation.
 *
 * @package Forte\Worker\Actions\Factories
 */
class ActionFactory implements ActionFactoryInterface
{
    /**
     * Create an instance of the VerifyArray class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return VerifyArray An instance of VerifyArray.
     */
    public static function createVerifyArray(...$parameters): VerifyArray
    {
        return new VerifyArray(...$parameters);
    }

    /**
     * Create an instance of the DirectoryDoesNotExist class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return DirectoryDoesNotExist An instance of DirectoryDoesNotExist.
     */
    public static function createDirectoryDoesNotExist(...$parameters): DirectoryDoesNotExist
    {
        return new DirectoryDoesNotExist(...$parameters);
    }

    /**
     * Create an instance of the DirectoryExists class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return DirectoryExists An instance of DirectoryExists.
     */
    public static function createDirectoryExists(...$parameters): DirectoryExists
    {
        return new DirectoryExists(...$parameters);
    }

    /**
     * Create an instance of the FileDoesNotExist class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileDoesNotExist An instance of FileDoesNotExist.
     */
    public static function createFileDoesNotExist(...$parameters): FileDoesNotExist
    {
        return new FileDoesNotExist(...$parameters);
    }

    /**
     * Create an instance of the FileExists class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileExists An instance of FileExists.
     */
    public static function createFileExists(...$parameters): FileExists
    {
        return new FileExists(...$parameters);
    }

    /**
     * Create an instance of the FileHasInstantiableClass class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileHasInstantiableClass An instance of FileHasInstantiableClass.
     */
    public static function createFileHasInstantiableClass(...$parameters): FileHasInstantiableClass
    {
        return new FileHasInstantiableClass(...$parameters);
    }

    /**
     * Create an instance of the FileHasValidEntries class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return FileHasValidEntries An instance of FileHasValidEntries.
     */
    public static function createFileHasValidEntries(...$parameters): FileHasValidEntries
    {
        return new FileHasValidEntries(...$parameters);
    }

    /**
     * Create an instance of the VerifyString class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return VerifyString An instance of VerifyString.
     */
    public static function createVerifyString(...$parameters): VerifyString
    {
        return new VerifyString(...$parameters);
    }

    /**
     * Create an instance of the ModifyArray class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ModifyArray An instance of ModifyArray.
     */
    public static function createModifyArray(...$parameters): ModifyArray
    {
        return new ModifyArray(...$parameters);
    }

    /**
     * Create an instance of the ChangeFileEntries class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ChangeFileEntries An instance of ChangeFileEntries.
     */
    public static function createChangeFileEntries(...$parameters): ChangeFileEntries
    {
        return new ChangeFileEntries(...$parameters);
    }

    /**
     * Create an instance of the CopyFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return CopyFile An instance of CopyFile.
     */
    public static function createCopyFile(...$parameters): CopyFile
    {
        return new CopyFile(...$parameters);
    }

    /**
     * Create an instance of the ModifyFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return ModifyFile An instance of ModifyFile.
     */
    public static function createModifyFile(...$parameters): ModifyFile
    {
        return new ModifyFile(...$parameters);
    }

    /**
     * Create an instance of the MoveDirectory class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return MoveDirectory An instance of MoveDirectory.
     */
    public static function createMoveDirectory(...$parameters): MoveDirectory
    {
        return new MoveDirectory(...$parameters);
    }

    /**
     * Create an instance of the MoveFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return MoveFile An instance of MoveFile.
     */
    public static function createMoveFile(...$parameters): MoveFile
    {
        return new MoveFile(...$parameters);
    }

    /**
     * Create an instance of the RemoveFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return RemoveFile An instance of RemoveFile.
     */
    public static function createRemoveFile(...$parameters): RemoveFile
    {
        return new RemoveFile(...$parameters);
    }

    /**
     * Create an instance of the RenameDirectory class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return RenameDirectory An instance of RenameDirectory.
     */
    public static function createRenameDirectory(...$parameters): RenameDirectory
    {
        return new RenameDirectory(...$parameters);
    }

    /**
     * Create an instance of the RenameFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return RenameFile An instance of RenameFile.
     */
    public static function createRenameFile(...$parameters): RenameFile
    {
        return new RenameFile(...$parameters);
    }

    /**
     * Create an instance of the UnzipFile class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return UnzipFile An instance of UnzipFile.
     */
    public static function createUnzipFile(...$parameters): UnzipFile
    {
        return new UnzipFile(...$parameters);
    }

    /**
     * Create an instance of the EmptyTransform class.
     *
     * @param mixed ...$parameters The construction parameters.
     *
     * @return EmptyTransform An instance of EmptyTransform.
     */
    public static function createEmptyTransform(...$parameters): EmptyTransform
    {
        return new EmptyTransform();
    }

    /**
     * Create an instance of the given Abstract subclass
     * name (full namespace).
     *
     * @param string $class The AbstractAction subclass
     * name to be created (full namespace).
     *
     * @param array $parameters
     *
     * @return AbstractAction An instance of the required
     * AbstractAction subclass.
     *
     * @throws WorkerException The given class parameter
     * is not a supported AbstractAction subclass name.
     */
    public static function create(string $class, ...$parameters): AbstractAction
    {
        try {
            switch ($class) {
                case VerifyArray::class:
                    return self::createVerifyArray(...$parameters);
                    break;
                case DirectoryDoesNotExist::class:
                    return self::createDirectoryDoesNotExist(...$parameters);
                    break;
                case DirectoryExists::class:
                    return self::createDirectoryExists(...$parameters);
                    break;
                case FileDoesNotExist::class:
                    return self::createFileDoesNotExist(...$parameters);
                    break;
                case FileExists::class:
                    return self::createFileExists(...$parameters);
                    break;
                case FileHasInstantiableClass::class:
                    return self::createFileHasInstantiableClass(...$parameters);
                    break;
                case FileHasValidEntries::class:
                    return self::createFileHasValidEntries(...$parameters);
                    break;
                case VerifyString::class:
                    return self::createVerifyString(...$parameters);
                    break;
                case ModifyArray::class:
                    return self::createModifyArray(...$parameters);
                    break;
                case ChangeFileEntries::class:
                    return self::createChangeFileEntries(...$parameters);
                    break;
                case CopyFile::class:
                    return self::createCopyFile(...$parameters);
                    break;
                case ModifyFile::class:
                    return self::createModifyFile(...$parameters);
                    break;
                case MoveDirectory::class:
                    return self::createMoveDirectory(...$parameters);
                    break;
                case MoveFile::class:
                    return self::createMoveFile(...$parameters);
                    break;
                case RemoveFile::class:
                    return self::createRemoveFile(...$parameters);
                    break;
                case RenameDirectory::class:
                    return self::createRenameDirectory(...$parameters);
                    break;
                case RenameFile::class:
                    return self::createRenameFile(...$parameters);
                    break;
                case UnzipFile::class:
                    return self::createUnzipFile(...$parameters);
                    break;
                case EmptyTransform::class:
                    return self::createEmptyTransform(...$parameters);
                    break;
                default:
                    throw new WorkerException("The given action type '$class' is not supported.");
            }
        } catch (\Throwable $error) {
            // Here we catch \Throwable instances to be sure that we catch \TypeError instances too.
            // These instances are thrown if a constructor is called with the wrong parameter types.
            throw new WorkerException(sprintf(
                "Impossible to create an instance of class '%s'. Reason: %s.",
                $class,
                $error->getMessage()
            ));
        }
    }
}