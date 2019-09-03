<?php

namespace Forte\Worker\Builders;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\FileHasValidConfigEntries;
use Forte\Worker\Runners\ProjectRunner;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Actions\Checks\Files\FileHasInstantiableClass;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use Forte\Worker\Actions\Transforms\Files\ChangeFileConfigEntries;
use Forte\Worker\Actions\Transforms\Files\CopyFile;
use Forte\Worker\Actions\Transforms\Files\UnzipFile;

/**
 * Class ProjectRunnerBuilder
 *
 * @package Forte\Worker\Builders
 */
class ProjectRunnerBuilder
{
    /**
     * @var ProjectRunner
     */
    protected $runner;

    /**
     * ProjectRunnerBuilder constructor.
     *
     * @param string $projectRootFolder The project root folder.
     */
    public function __construct(string $projectRootFolder)
    {
        $this->setRunner(new ProjectRunner($projectRootFolder));
    }

    /**
     * It initializes the project folder from the given zip file.
     * It unzips the file in the base project folder specified in
     * the constructor.
     *
     * @param string $zipFilePath The zip file to unzip.
     *
     * @return self
     */
    public function initFromZipFile(string $zipFilePath): self
    {
        $this->addAction($this->getUnzipFileAction($zipFilePath));

        return $this;
    }

    /**
     * Returns an instance of the UnzipFile object.
     *
     * @param string $zipFilePath The zip file to unzip.
     *
     * @return UnzipFile
     */
    public function getUnzipFileAction(string $zipFilePath): UnzipFile
    {
        $fullProjectPath = $this->runner->getProjectFolder();
        return (new UnzipFile())
            ->addBeforeAction(new FileExists($zipFilePath))
            ->addAfterAction(new FileExists($fullProjectPath))
            ->open($zipFilePath)
            ->extractTo($fullProjectPath)
        ;
    }

    /**
     * Add an action to copy the given source file. If no target folder is specified,
     * the source file base folder will be used. If no target file name is specified,
     * the source file name with the add of the suffix "_COPY" will be used.
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
        $this->addAction(
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
     * Add an action, which only checks if the given class file path contains
     * an instantiable class, whose name is the given class name.
     *
     * @param string $classFilePath The class file path.
     * @param string $className The class name.
     *
     * @return ProjectRunnerBuilder
     */
    public function hasInstantiableClass(string $classFilePath, string $className): self
    {
        $this->addAction(
            (new EmptyTransform())
                ->addBeforeAction(new FileHasInstantiableClass($classFilePath, $className))
        );

        return $this;
    }

    /**
     * Modify the given config key with the given value in the specified file.
     * If the file does not have the specified key, this method will add it
     * to the file. Multi-level configuration keys are supported (each level
     * separated by the constant FileParser::CONFIG_LEVEL_SEPARATOR - a dot).
     * (e.g. key1.key2.key3=value3)
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values -> constants FileParser::CONTENT_TYPE_XXX).
     * @param string $key The key to modify.
     * @param mixed $value The new key value.
     *
     * @return ProjectRunnerBuilder
     */
    public function modifyConfigKey(string $filePath, string $contentType, string $key, $value): self
    {
        $this->addAction(
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
     * (e.g. key1.key2.key3=value3)
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values -> constants FileParser::CONTENT_TYPE_XXX).
     * @param string $key The key to add.
     * @param mixed $value The new key value.
     *
     * @return ProjectRunnerBuilder
     */
    public function addConfigKey(string $filePath, string $contentType, string $key, $value): self
    {
        $this->addAction(
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
     * Remove the given config key with the given value from the specified file.
     * Multi-level configuration keys are supported (each level separated by the
     * constant FileParser::CONFIG_LEVEL_SEPARATOR - a dot).
     * (e.g. key1.key2.key3=value3)
     *
     * @param string $filePath The file to modify.
     * @param string $contentType The content type (accepted values -> constants FileParser::CONTENT_TYPE_XXX).
     * @param string $key The key to remove.
     *
     * @return ProjectRunnerBuilder
     */
    public function removeConfigKey(string $filePath, string $contentType, string $key): self
    {
        $this->addAction(
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
     * Convert the given file name to a full-path file name, by
     * using the base project folder configured with this runner
     * (e.g. "base.php" -> "/base/project/folder/base.php").
     *
     * @param string $fileName The file name to be converted
     * to a project full path.
     *
     * @return string
     */
    public function getFilePathInProject(string $fileName): string
    {
        return $this->runner->getProjectFolder() . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Get the ProjectRunner that represents all the actions
     * built by this instance.
     *
     * @return ProjectRunner
     */
    public function getRunner(): ProjectRunner
    {
        return $this->runner;
    }

    /**
     * Set the ProjectRunner that represents all the actions
     * built by this instance.
     *
     * @param ProjectRunner $runner
     *
     * @return void
     */
    protected function setRunner(ProjectRunner $runner): void
    {
        $this->runner = $runner;
    }

    /**
     * Add an action to the project runner.
     *
     * @param AbstractAction $action the action to add.
     *
     * @return AbstractAction The action added to the list of
     * runnable actions.
     */
    protected function addAction(AbstractAction $action): AbstractAction
    {
        $this->getRunner()->addAction($action);

        return $action;
    }
}
