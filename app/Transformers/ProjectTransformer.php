<?php

namespace Forte\Api\Generator\Transformers;

/**
 * Class ProjectTransformer. A class that applies a set of transformations to a Laravel project.
 *
 * @package Forte\Api\Generator\Transformers
 */
class ProjectTransformer extends AbstractTransformer
{
    /**
     * @var string
     */
    protected $projectRootFolder;

    /**
     * @var string
     */
    protected $deploymentParentFolder;

    /**
     * ProjectTransformer constructor.
     *
     * @param string $projectRootFolder The project root folder
     * @param string $deploymentParentFolder
     */
    public function __construct(string $projectRootFolder, string $deploymentParentFolder)
    {
        $this->projectRootFolder = rtrim($projectRootFolder, DIRECTORY_SEPARATOR);
        $this->deploymentParentFolder = rtrim($deploymentParentFolder, DIRECTORY_SEPARATOR);
    }

    /**
     * Returns the project root folder.
     *
     * @return string
     */
    public function getProjectRootFolder(): string
    {
        return $this->projectRootFolder;
    }

    /**
     * Returns the project deployment parent folder.
     *
     * @return string
     */
    public function getDeploymentParentFolder(): string
    {
        return $this->deploymentParentFolder;
    }

    /**
     * Returns the full-path project folder.
     *
     * @return string
     */
    public function getFullPathProjectFolder(): string
    {
        return
            $this->deploymentParentFolder .
            DIRECTORY_SEPARATOR .
            $this->projectRootFolder;
    }
}