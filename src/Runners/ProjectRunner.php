<?php

namespace Forte\Worker\Runners;

/**
 * Class ProjectRunner. A class that applies a set of actions
 * to a configured project.
 *
 * @package Forte\Worker\Runners
 */
class ProjectRunner extends AbstractRunner
{
    /**
     * The project root folder
     * (e.g. /path/to/installation/folder/local/projects/my-project).
     *
     * @var string
     */
    protected $projectFolder;

    /**
     * The folder where this project is installed
     * (e.g. /path/to/installation/folder/local/projects).
     *
     * @var string
     */
    protected $installationFolder;

    /**
     * The relative folder (e.g. 'my-project').
     *
     * @var string
     */
    protected $relativeFolder;

    /**
     * ProjectRunner constructor. This class allows to apply a given set of
     * actions to the configured project.
     *
     * @param string $projectFolder The project root folder
     * (e.g. /path/to/installation/folder/local/projects/my-project)
     */
    public function __construct(string $projectFolder)
    {
        // Set the full project path
        $this->projectFolder = rtrim($projectFolder, DIRECTORY_SEPARATOR);
        // Set the installation folder
        $this->installationFolder = dirname($projectFolder);
        // Set the relative project path (last-level folder)
        $pathParts = explode(DIRECTORY_SEPARATOR, $this->projectFolder);
        $this->relativeFolder = array_pop($pathParts);
    }

    /**
     * Return the project folder.
     *
     * @return string
     */
    public function getProjectFolder(): string
    {
        return $this->projectFolder;
    }

    /**
     * Return the project installation folder.
     *
     * @return string
     */
    public function getInstallationFolder(): string
    {
        return $this->installationFolder;
    }

    /**
     * Return the relative project folder.
     *
     * @return string
     */
    public function getRelativeFolder(): string
    {
        return $this->relativeFolder;
    }
}
