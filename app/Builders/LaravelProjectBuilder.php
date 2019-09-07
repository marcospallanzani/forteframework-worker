<?php

namespace Forte\Api\Generator\Builders;

use Forte\Worker\Actions\Checks\Arrays\VerifyArray;
use Forte\Worker\Actions\Checks\Files\FileHasValidEntries;
use Forte\Worker\Builders\ProjectRunnerBuilder;
use Forte\Worker\Helpers\FileParser;

/**
 * Class LaravelProjectBuilder. Class in charge of building a project transformer
 * with specific transformations for a Laravel project.
 *
 * @package Forte\Api\Generator\Builders
 */
class LaravelProjectBuilder extends ProjectRunnerBuilder
{
    /**
     * LaravelProjectBuilder constructor.
     *
     * @param string $projectRootFolder The project root folder.
     * where all generated projects will be installed.
     */
    public function __construct(string $projectRootFolder)
    {
        parent::__construct($projectRootFolder);
//TODO I should be able to pass the zip file path as a parameter
//TODO replace the init from zip with a new Transform instance that checks out the project from git
        $this
            ->addLaravelUnzipFileTransform('skeleton/forteframework-api-skeleton.zip')
            ->copyFileTo($this->getFilePathInProject('.env.example'), '.env')
            ->copyFileTo($this->getFilePathInProject('.env.testing.example'), '.env.testing')
            ->hasInstantiableClass($this->getFilePathInProject('app/Exceptions/Handler.php'), 'Handler')
            ->hasInstantiableClass($this->getFilePathInProject('app/Http/Kernel.php'), 'Kernel')
        ;
    }

    /**
     * Adds a custom AbstractTransform subclass instance to unzip a
     * Laravel zip into the configured class project folder.
     *
     * @param string $zipFilePath The file path of the zip containing a base Laravel project.
     *
     * @return LaravelProjectBuilder
     */
    public function addLaravelUnzipFileTransform(string $zipFilePath): self
    {
        $this->addAction(
            $this
                ->getUnzipFileAction($zipFilePath)
                ->addAfterAction(
                    (new FileHasValidEntries())
                        ->hasKeyWithNonEmptyValue("require.laravel/framework")
                        ->hasKey("require.forteframework/api")
                        ->hasKey("require.php")
                        ->hasKeyWithValue("type", "project", VerifyArray::CHECK_EQUALS)
                        ->contentType(FileParser::CONTENT_TYPE_JSON)
                        ->setPath($this->getFilePathInProject('composer.json'))
                )
        );

        return $this;
    }
}
