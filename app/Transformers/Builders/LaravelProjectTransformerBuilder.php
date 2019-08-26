<?php

namespace Forte\Api\Generator\Transformers\Builders;

/**
 * Class LaravelProjectTransformerBuilder. Class in charge of building a project transformer
 * with specific transformations for a Laravel project.
 *
 * @package Forte\Api\Generator\Transformers\Builders
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

        $this
            ->initFromZipFile('skeleton/forteframework-api-skeleton.zip')
            ->copyFileTo($this->getFilePathInProject('.env.example'), '.env')
            ->copyFileTo($this->getFilePathInProject('.env.testing.example'), '.env.testing')();
    }
}
