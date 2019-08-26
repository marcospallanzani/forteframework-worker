<?php

namespace Forte\Api\Generator\Transformers\Builders;


use Forte\Api\Generator\Transformers\ProjectTransformer;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;
use Forte\Api\Generator\Transformers\Transforms\File\Copy;
use Forte\Api\Generator\Transformers\Transforms\File\Unzip;

/**
 * Class ProjectTransformerBuilder
 *
 * @package Forte\Api\Generator\Transformers\Builders
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
     * @param string $deploymentParentFolder The deployment parent folder
     * where all generated projects will be installed.
     */
    public function __construct(string $projectRootFolder, string $deploymentParentFolder)
    {
        $this->setTransformer(new ProjectTransformer($projectRootFolder, $deploymentParentFolder));
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
        $this->addTransform(
            (new Unzip())
                ->open($zipFilePath)
                ->extractTo($this->transformer->getFullPathProjectFolder())
        );

        return $this;
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
        $this->addTransform(
            (new Copy())
                ->copy($sourceFilePath)
                ->toFolder($targetFolder)
                ->withName($targeFileName)
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
        return $this->transformer->getFullPathProjectFolder() . DIRECTORY_SEPARATOR . $fileName;
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
     * @param AbstractTransform $transform the transform to add.
     *
     * @return AbstractTransform The transformation added
     * to the data (so methods can be chained on it).
     */
    protected function addTransform(AbstractTransform $transform): AbstractTransform
    {
        $this->getTransformer()->addTransform($transform);

        return $transform;
    }
}
