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
     * ProjectRunner constructor. This class allows to apply a given set of
     * actions to the configured project.
     *
     * @param string $projectFolder The project root folder
     * (e.g. /path/to/installation/folder/local/projects/my-project)
     */
    public function __construct(string $projectFolder)
    {
        $this->projectFolder      = rtrim($projectFolder, DIRECTORY_SEPARATOR);
        $this->installationFolder = dirname($projectFolder);
    }

    /**
     * Returns the project folder.
     *
     * @return string
     */
    public function getProjectFolder(): string
    {
        return $this->projectFolder;
    }

    /**
     * Returns the project installation folder.
     *
     * @return string
     */
    public function getInstallationFolder(): string
    {
        return $this->installationFolder;
    }
}
