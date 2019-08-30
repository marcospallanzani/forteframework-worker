<?php

namespace Forte\Api\Generator\Builders;

use Forte\Api\Generator\Filters\Arrays\VerifyArray;
use Forte\Api\Generator\Checkers\Checks\File\FileHasValidConfigEntries;
use Forte\Api\Generator\Helpers\FileParser;

/**
 * Class LaravelProjectTransformerBuilder. Class in charge of building a project transformer
 * with specific transformations for a Laravel project.
 *
 * @package Forte\Api\Generator\Builders
 */
class LaravelProjectTransformerBuilder extends ProjectTransformerBuilder
{
    /**
     * LaravelProjectTransformerBuilder constructor.
     *
     * @param string $projectRootFolder The project root folder.
     * @param string $deploymentParentFolder The deployment parent folder
     * where all generated projects will be installed.
     */
    public function __construct(string $projectRootFolder, string $deploymentParentFolder)
    {
        parent::__construct($projectRootFolder, $deploymentParentFolder);
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
     * @return LaravelProjectTransformerBuilder
     */
    public function addLaravelUnzipFileTransform(string $zipFilePath): self
    {
        $this->addTransform(
            $this
                ->getUnzipFileTransform($zipFilePath)
                ->addAfterCheck(
                    (new FileHasValidConfigEntries())
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
