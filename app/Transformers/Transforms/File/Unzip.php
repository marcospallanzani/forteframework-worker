<?php

namespace Forte\Api\Generator\Transformers\Transforms\File;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;
use Zend\Validator\File\NotExists;

/**
 * Class Unzip
 *
 * @package Forte\Api\Generator\Transformers\Transforms\File
 */
class Unzip extends AbstractTransform
{
    /**
     * @var string
     */
    protected $zipFilePath;

    /**
     * @var string
     */
    protected $extractToPath;

    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool
     *
     * @throws TransformException
     */
    public function isValid(): bool
    {
        // The zip file path cannot be empty
        if (empty($this->zipFilePath)) {
            throw new TransformException($this, "You must specify the zip file path.");
        }

        // We check if the zip file exists
        $notExists = new NotExists();
        if ($notExists->isValid($this->zipFilePath)) {
            throw new TransformException($this, sprintf(
                "The zip file '%s' does not exist.",
                $this->zipFilePath
            ));
        }

        return true;
    }

    /**
     * Apply the transformation. It unzips the configured zip files.
     * If no extraction folder is set, the zip file folder will be used.
     *
     * @return bool
     *
     * @throws GeneratorException
     * @throws TransformException
     */
    public function transform(): bool
    {
        if ($this->isValid()) {

            $info = pathinfo($this->zipFilePath);

            // We check if an extraction folder is set:
            // if empty, we use the folder of the set zip file.
            if (empty($this->extractToPath)) {
                $this->extractToPath = $info['dirname'];
            }

            $zip = new \ZipArchive();
            if ($zip->open($this->zipFilePath) === TRUE) {
                $zip->extractTo($this->extractToPath);
                $zip->close();
            } else {
                throw new GeneratorException(sprintf(
                    "Impossible to unzip the given ZIP file '%s'",
                    $this->zipFilePath
                ));
            }

            return true;
        }

        return false;
    }

    /**
     * Opens the specified zip file.
     *
     * @param string $zipFilePath The zip file path
     *
     * @return Unzip
     */
    public function open(string $zipFilePath): self
    {
        $this->zipFilePath = $zipFilePath;

        return $this;
    }

    /**
     * Extracts the zip file to the specified destination path.
     *
     * @param string $extractToPath The destination extraction path
     *
     * @return Unzip
     */
    public function extractTo(string $extractToPath): self
    {
        $this->extractToPath = $extractToPath;

        return $this;
    }


}