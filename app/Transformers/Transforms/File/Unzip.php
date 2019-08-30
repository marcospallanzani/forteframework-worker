<?php

namespace Forte\Api\Generator\Transformers\Transforms\File;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;

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
     * @return bool Returns true if this AbstractTransform subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws TransformException
     */
    public function isValid(): bool
    {
        // The zip file path cannot be empty
        if (empty($this->zipFilePath)) {
            $this->throwTransformException($this, "You must specify the zip file path.");
        }

        return true;
    }

    /**
     * Apply the sub-class transformation action.
     *
     * @return bool Returns true if the transform action implemented by
     * this AbstractTransform subclass instance has been successfully
     * applied; false otherwise.
     *
     * @throws GeneratorException
     * @throws TransformException
     */
    protected function apply(): bool
    {
        // We check if the zip file exists
        $this->checkFileExists($this->zipFilePath);

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
            $this->throwTransformException(
                $this,
                "Impossible to unzip the given ZIP file '%s'",
                $this->zipFilePath
            );
        }

        return true;
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

    /**
     * Returns a string representation of this AbstractTransform subclass instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return sprintf("Unzip file '%s' to '%s'.", $this->zipFilePath, $this->extractToPath);
    }
}
