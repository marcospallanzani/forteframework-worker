<?php

namespace Forte\Worker\Builders;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\FileHasValidConfigEntries;
use Forte\Worker\Transformers\ProjectTransformer;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Transformers\Transforms\EmptyTransform;
use Forte\Worker\Transformers\Transforms\Files\ChangeFileConfigEntries;
use Forte\Worker\Transformers\Transforms\Files\CopyFile;
use Forte\Worker\Transformers\Transforms\Files\UnzipFile;

/**
 * Class ProjectTransformerBuilder
 *
 * @package Forte\Worker\Builders
 */
class ProjectTransformerBuilder
{
    /**
     * @var ProjectTransformer
     */
    protected $transformer;

    /**
     * ProjectTransformerBuilder constructor.
     *
     * @param string $projectRootFolder The project root folder.
     */
    public function __construct(string $projectRootFolder)
    {
        $this->setTransformer(new ProjectTransformer($projectRootFolder));
    }

    /**
     * It initializes the project folder from the given zip file.
     * It unzips the file in the base project folder specified in the constructor.
     *
     * @param string $zipFilePath The zip file to unzip.
     *
     * @return self
     */
    public function initFromZipFile(string $zipFilePath): self
    {
        $this->addTransform($this->getUnzipFileTransform($zipFilePath));

        return $this;
    }

    /**
     * Returns an instance of the Unzip transform object.
     *
     * @param string $zipFilePath The zip file to unzip.
     *
     * @return UnzipFile
     */
    public function getUnzipFileTransform(string $zipFilePath): UnzipFile
    {
        $fullProjectPath = $this->transformer->getProjectFolder();
        return (new UnzipFile())
            ->addBeforeAction(new FileExists($zipFilePath))
            ->addAfterAction(new FileExists($fullProjectPath))
            ->open($zipFilePath)
            ->extractTo($fullProjectPath)
        ;
    }

    /**
     * Add a Transform instance to copy the given source file.
     * If no target folder is specified, the source file base folder will be used.
     * If no target file name is specified, the source file name with the add of the
     * suffix "_COPY" will be used.
     *
     * @param string $sourceFilePath The file full path to be copied.
     * @param string|null $targeFileName The destination file name.
     * @param string|null $targetFolder The destination folder.
     *
     * @return self
     */
    public function copyFileTo(
        string $sourceFilePath,
        string $targeFileName = '',
        string $targetFolder = ''
    ): self
    {
        $copy = new CopyFile();
        $this->addTransform(
            $copy
                ->copy($sourceFilePath)
                ->toFolder($targetFolder)
                ->withName($targeFileName)
                ->addBeforeAction(new FileExists($sourceFilePath))
                ->addAfterAction(new FileExists($copy->getDestinationFilePath()))
        );
        return $this;
    }

    /**
     * Add an empty Transform instance to only check if the given class file path contains
     * an instantiable class, whose name is the given class name.
     *
     * @param string $classFilePath The class file path.
     * @param string $className The class name.
     *
     * @return ProjectTransformerBuilder
     */
    public function hasInstantiableClass(string $classFilePath, string $className): self
    {
        $this->addTransform(
            (new EmptyTransform())
                ->addBeforeAction(new FileHasInstantiableClass($classFilePath, $className))
        );

        return $this;
    }

    /**
     * Add the given config key with the given value to the specified file.
     * If the file does not have the specified key, this method will add it
     * to the file. Multi-level configuration keys are supported (each level
     * separated by the constant FileParser::CONFIG_LEVEL_SEPARATOR - a dot).
     * e.g. key1.key2.key3=value3
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values -> constants FileParser::CONTENT_TYPE_XXX).
     * @param string $key The key to modify.
     * @param mixed $value The new key value.
     *
     * @return ProjectTransformerBuilder
     */
    public function modifyConfigKey(string $filePath, string $contentType, string $key, $value): self
    {
        $this->addTransform(
            (new ChangeFileConfigEntries($filePath, $contentType))
                ->modifyConfigKeyWithValue($key, $value)
                ->addAfterAction(
                    (new FileHasValidConfigEntries($filePath, $contentType))
                        ->hasKeyWithValue($key, $value, VerifyArray::CHECK_EQUALS)
                )
        );

        return $this;
    }

    /**
     * Add the given config key with the given value to the specified file.
     * Multi-level configuration keys are supported (each level separated
     * by the constant FileParser::CONFIG_LEVEL_SEPARATOR - a dot).
     * e.g. key1.key2.key3=value3
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values -> constants FileParser::CONTENT_TYPE_XXX).
     * @param string $key The key to add.
     * @param mixed $value The new key value.
     *
     * @return ProjectTransformerBuilder
     */
    public function addConfigKey(string $filePath, string $contentType, string $key, $value): self
    {
        $this->addTransform(
            (new ChangeFileConfigEntries($filePath, $contentType))
                ->addConfigKeyWithValue($key, $value)
                ->addAfterAction(
                    (new FileHasValidConfigEntries($filePath, $contentType))
                        ->hasKeyWithValue($key, $value, VerifyArray::CHECK_EQUALS)
                )
        );

        return $this;
    }

    /**
     * Add the given config key with the given value to the specified file.
     * Multi-level configuration keys are supported (each level separated
     * by the constant FileParser::CONFIG_LEVEL_SEPARATOR - a dot).
     * e.g. key1.key2.key3=value3
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values -> constants FileParser::CONTENT_TYPE_XXX).
     * @param string $key The key to remove.
     *
     * @return ProjectTransformerBuilder
     */
    public function removeConfigKey(string $filePath, string $contentType, string $key): self
    {
        $this->addTransform(
            (new ChangeFileConfigEntries($filePath, $contentType))
                ->removeConfigKey($key)
                ->addAfterAction(
                    (new FileHasValidConfigEntries($filePath, $contentType))
                        ->doesNotHaveKey($key)
                )
        );

        return $this;
    }

    /**
     * Converts the given file name to a full-path file name, by using the configured
     * transformer base project folder. (e.g. "base.php" -> "/base/project/folder/base.php")
     *
     * @param string $fileName The file name to be converted to a project full path.
     *
     * @return string
     */
    public function getFilePathInProject(string $fileName): string
    {
        return $this->transformer->getProjectFolder() . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Get the ProjectTransformer that represents all the
     * transformations built by this instance.
     *
     * @return ProjectTransformer
     */
    public function getTransformer(): ProjectTransformer
    {
        return $this->transformer;
    }

    /**
     * Set the ProjectTransformer that represents all the
     * transformations built by this instance.
     *
     * @param ProjectTransformer $transformer
     *
     * @return void
     */
    protected function setTransformer(ProjectTransformer $transformer): void
    {
        $this->transformer = $transformer;
    }

    /**
     * Add a transformation to the project.
     *
     * @param AbstractAction $transform the transform to add.
     *
     * @return AbstractAction The action added to the data
     * (so methods can be chained on it).
     */
    protected function addTransform(AbstractAction $transform): AbstractAction
    {
        $this->getTransformer()->addAction($transform);

        return $transform;
    }
}
