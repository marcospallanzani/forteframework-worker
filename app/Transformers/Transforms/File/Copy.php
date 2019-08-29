<?php

namespace Forte\Api\Generator\Transformers\Transforms\File;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Filters\Files\Copy as CopyFilter;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;
use Zend\Filter\Exception\RuntimeException;

/**
 * Class Copy
 *
 * @package Forte\Api\Generator\Transformers\Transforms\File
 */
class Copy extends AbstractTransform
{
    /**
     * @var string
     */
    protected $originFilePath;

    /**
     * @var string
     */
    protected $destinationFolder;

    /**
     * @var string
     */
    protected $destinationFileName;

    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws GeneratorException
     * @throws TransformException
     */
    public function isValid(): bool
    {
        // The origin file path cannot be empty
        if (empty($this->originFilePath)) {
            $this->throwTransformException($this, "You must specify a file to be copied.");
        }

        // If no destination folder is specified, or it is the same as the origin folder,
        // AND the destination file name is the same as the original file name,
        // THEN we throw an error
        $destinationFilePath = $this->getDestinationFilePath();
        if (rtrim($this->originFilePath, DIRECTORY_SEPARATOR) === $destinationFilePath) {
            $this->throwTransformException(
                $this,
                "The origin file '%s' and the specified destination file '%s' cannot be the same.",
                $this->originFilePath,
                $destinationFilePath
            );
        }

        return true;
    }

    /**
     * Apply the transformation.
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance has been successfully applied; false otherwise.
     *
     * @throws GeneratorException
     * @throws TransformException
     */
    public function transform(): bool
    {
        if ($this->isValid()) {
            // We check if the origin file exists
            $this->checkFileExists($this->originFilePath);

            try {
                // We run the pre-transform checks
                $this->runAndReportBeforeChecks(true);

                $info = pathinfo($this->originFilePath);

                // We check if a destination folder is set:
                // if empty, we use the same origin file folder
                if (empty($this->destinationFolder)) {
                    $this->destinationFolder = $info['dirname'];
                }

                // We check if the destination file name is set:
                // if empty, we use the origin file name with a "_Copy" suffix
                if (empty($this->destinationFileName)) {
                    $this->destinationFileName = $info['filename'] . "_COPY" . $info['extension'];
                }

                $copyFilter = new CopyFilter([
                    'target' => $this->getDestinationFilePath(),
                    'overwrite' => true
                ]);
                $copyFilter->filter($this->originFilePath);

                // We run the post-transform checks
                $this->runAndReportAfterChecks(true);

                return true;
            } catch (RuntimeException $runtimeException) {
                $this->throwTransformException(
                    $this,
                    "An error occurred while copying file '%s' to '%s'. Error message is: '%s'",
                    $this->originFilePath,
                    $this->destinationFolder,
                    $runtimeException->getMessage()
                );
            }
        }

        return false;
    }

    /**
     * Sets the origin file path.
     *
     * @param string $originFilePath The full file path to be copied
     *
     * @return Copy
     */
    public function copy(string $originFilePath): self
    {
        $this->originFilePath = $originFilePath;

        return $this;
    }

    /**
     * Sets the destination folder.
     *
     * @param string $destinationFolder The full destination directory path
     *
     * @return Copy
     */
    public function toFolder(string $destinationFolder): self
    {
        $this->destinationFolder = rtrim($destinationFolder, DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * Sets the destination file name.
     *
     * @param string $fileName The destination file name
     *
     * @return Copy
     */
    public function withName(string $fileName): self
    {
        $this->destinationFileName = trim($fileName, DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * Creates the full destination file path as a concatenation
     * of the destination folder and the destination name.
     *
     * @param string $defaultDestinationFolder If the class destination folder is empty, t
     * he given default destination folder will be used.
     *
     * @return string The full destination file path
     * (concat destination folder and destination name).
     */
    public function getDestinationFilePath(string $defaultDestinationFolder = ""): string
    {
        // If no default destination folder is specified, we try to use the default class destination folder
        if (empty($defaultDestinationFolder)) {
            $defaultDestinationFolder = $this->getDefaultDestinationFolder();
        }

        if (empty($this->destinationFolder)) {
            if (!empty($defaultDestinationFolder)) {
                return
                    rtrim($defaultDestinationFolder, DIRECTORY_SEPARATOR) .
                    DIRECTORY_SEPARATOR .
                    $this->destinationFileName
                    ;
            }
            return $this->destinationFileName;
        }

        return
            $this->destinationFolder .
            DIRECTORY_SEPARATOR .
            $this->destinationFileName
            ;
    }

    /**
     * Returns the default destination folder (i.e. same folder as the original file path).
     *
     * @return string
     */
    public function getDefaultDestinationFolder(): string
    {
        $info = pathinfo($this->originFilePath);
        if (is_array($info) && array_key_exists('dirname', $info)) {
            return $info['dirname'];
        }
        return "";
    }

    /**
     * Returns a string representation of this AbstractTransform subclass instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return sprintf("Copy file '%s' to '%s'.", $this->originFilePath, $this->getDestinationFilePath());
    }
}
