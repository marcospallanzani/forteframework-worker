<?php

namespace Forte\Worker\Transformers\Transforms\File;

use Forte\Worker\Exceptions\GeneratorException;
use Forte\Worker\Exceptions\TransformException;
use Forte\Worker\Transformers\Transforms\AbstractTransform;

/**
 * Class Unzip
 *
 * @package Forte\Worker\Transformers\Transforms\File
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
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if an Unzip instance was well configured;
     * false otherwise.
     *
     * @throws TransformException If an Unzip instance was not well configured.
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
     * @return bool True if the transform action implemented by this
     * Unzip instance was successfully applied; false otherwise.
     *
     * @throws GeneratorException If a general or validation error occurred.
     * @throws TransformException If the transformation action failed.
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
     * Open the specified zip file.
     *
     * @param string $zipFilePath The zip file path.
     *
     * @return Unzip
     */
    public function open(string $zipFilePath): self
    {
        $this->zipFilePath = $zipFilePath;

        return $this;
    }

    /**
     * Extract the zip file to the specified destination path.
     *
     * @param string $extractToPath The destination extraction path.
     *
     * @return Unzip
     */
    public function extractTo(string $extractToPath): self
    {
        $this->extractToPath = $extractToPath;

        return $this;
    }

    /**
     * Return a human-readable string representation of this Unzip instance.
     *
     * @return string A human-readable string representation of this Unzip instance.
     */
    public function stringify(): string
    {
        return sprintf("Unzip file '%s' to '%s'.", $this->zipFilePath, $this->extractToPath);
    }
}
