<?php

namespace Forte\Worker\Builders;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Runners\ProjectRunner;
use Forte\Worker\Actions\Transforms\EmptyTransform;

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
        $this->reset($projectRootFolder);
    }

    /**
     * Add an action to decompress the given zip file. It unzips the file in
     * the base project folder specified in the constructor.
     *
     * @param string $zipFilePath The zip file to unzip.
     *
     * @return self
     */
    public function unzipFile(string $zipFilePath): self
    {
        $fullProjectPath = $this->runner->getProjectFolder();

        $this->addAction(
            /** main action */
            ActionFactory::createUnzipFile()->open($zipFilePath)->extractTo($fullProjectPath),
            /** pre-run actions */
            [ActionFactory::createFileExists($zipFilePath)],
            /** post-run actions */
            [ActionFactory::createFileExists($fullProjectPath)]
        );

        return $this;
    }

    /**
     * Add an action to check if the given path corresponds to an existing directory.
     *
     * @param string $path The directory path to be checked.
     *
     * @return self
     */
    public function dirExists(string $path): self
    {
        $this->addAction(
            /** main action */
            ActionFactory::createDirectoryExists($path),
            /** pre-run actions */
            [],
            /** post-run actions */
            []
        );

        return $this;
    }

    /**
     * Add an action to copy the given source file. If no target folder is specified,
     * the source file base folder will be used. If no target file name is specified,
     * the source file name with the add of the suffix "_COPY" will be used.
     *
     * @param string $sourceFilePath The file full path to be copied.
     * @param string|null $targetFileName The destination file name.
     * @param string|null $targetFolder The destination folder.
     *
     * @return self
     */
    public function copyFileTo(
        string $sourceFilePath,
        string $targetFileName = '',
        string $targetFolder = ''
    ): self
    {
        $copy = ActionFactory::createCopyFile()
            ->copy($sourceFilePath)
            ->toFolder($targetFolder)
            ->withName($targetFileName)
        ;

        $this->addAction(
            /** main action */
            $copy,
            /** pre-run actions */
            [ActionFactory::createFileExists($sourceFilePath)],
            /** post-run actions */
            [ActionFactory::createFileExists($copy->getDestinationFilePath())]
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
        /** main action */
            new EmptyTransform(),
            /** pre-run actions */
            [ActionFactory::createFileHasInstantiableClass($classFilePath, $className)]
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
            /** main action */
            ActionFactory::createChangeConfigFileEntries($filePath, $contentType)->modifyKeyWithValue($key, $value),
            /** pre-run actions */
            [],
            /** post-run actions */
            [
                ActionFactory::createConfigFileHasValidEntries($filePath, $contentType)
                    ->hasKeyWithValue(
                        $key,
                        $value,
                        VerifyArray::CHECK_EQUALS
                    )
            ]
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
            /** main action */
            ActionFactory::createChangeConfigFileEntries($filePath, $contentType)->addKeyWithValue($key, $value),
            /** pre-run actions */
            [],
            /** post-run actions */
            [
                ActionFactory::createConfigFileHasValidEntries($filePath, $contentType)
                    ->hasKeyWithValue(
                        $key,
                        $value,
                        VerifyArray::CHECK_EQUALS
                    )
            ]
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
            /** main action */
            ActionFactory::createChangeConfigFileEntries($filePath, $contentType)->removeKey($key),
            /** pre-run actions */
            [],
            /** post-run actions */
            [ActionFactory::createConfigFileHasValidEntries($filePath, $contentType)->doesNotHaveKey($key)]
        );

        return $this;
    }

    /**
     * Add an action to the project runner.
     *
     * @param AbstractAction $action The action to add.
     * @param array $preRunActions A list of pre-run actions for the given action.
     * @param array $postRunActions A list of post-run actions for the given action.
     *
     * @return AbstractAction The action added to the list of
     * runnable actions.
     */
    public function addAction(
        AbstractAction $action,
        array $preRunActions = array(),
        array $postRunActions = array()
    ): AbstractAction
    {
        // Add the pre-run actions
        foreach ($preRunActions as $preRunAction) {
            if ($preRunAction instanceof AbstractAction) {
                $action->addBeforeAction($preRunAction);
            }
        }

        // Add the post-run actions
        foreach ($postRunActions as $postRunAction) {
            if ($postRunAction instanceof AbstractAction) {
                $action->addAfterAction($postRunAction);
            }
        }

        // Add the main action
        $this->getRunner()->addAction($action);

        return $action;
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
//TODO WE SHOULD REFACTOR THE FOLLOWING TWO METHODS WITH A MORE GENERAL METHOD THAT CAN HANDLE THE CRITICAL STATUS AS WELL
    /**
     * Set the "fatal" status with the given flag for all registered actions.
     *
     * @param bool $fatal The desired "fatal" status (true, all actions will
     * be marked as fatal).
     *
     * @return ProjectRunnerBuilder
     */
    public function setFatalStatusForAllActions(bool $fatal = true): self
    {
        foreach ($this->runner->getActions() as &$action) {
            if ($action instanceof AbstractAction) {
                $action->setIsFatal($fatal);
            }
        }

        return $this;
    }
//TODO WE SHOULD REFACTOR THE FOLLOWING TWO METHODS WITH A MORE GENERAL METHOD THAT CAN HANDLE THE CRITICAL STATUS AS WELL
    /**
     * Set the "success-required" status with the given flag for all
     * registered actions.
     *
     * @param bool $successRequired The desired "success-required"
     * status (true, all actions will be marked as success-required).
     *
     * @return ProjectRunnerBuilder
     */
    public function setSuccessRequiredForAllActions(bool $successRequired = true): self
    {
        foreach ($this->runner->getActions() as &$action) {
            if ($action instanceof AbstractAction) {
                $action->setIsFatal($successRequired);
            }
        }

        return $this;
    }

    /**
     * Reset the current runner with a new instance for the given
     * project root folder.
     *
     * @param string $projectRootFolder The project root folder.
     */
    protected function reset(string $projectRootFolder): void
    {
        $this->runner = new ProjectRunner($projectRootFolder);
    }
}
