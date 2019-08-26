<?php

namespace Forte\Api\Generator\Transformers\Transforms\File;

use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Filters\File\Copy as CopyFilter;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;
use Zend\Filter\Exception\RuntimeException;
use Zend\Validator\File\NotExists;

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
     * @return bool
     *
     * @throws TransformException
     */
    public function isValid(): bool
    {
        // The origin file path cannot be empty
        if (empty($this->originFilePath)) {
            throw new TransformException($this, "You must specify a file to be copied.");
        }

        // We check if the origin file exists
        $notExists = new NotExists();
        if ($notExists->isValid($this->originFilePath)) {
            throw new TransformException($this, sprintf(
                "The origin file '%s' does not exist.",
                $this->originFilePath
            ));
        }

        // If no destination folder is specified, or it is the same as the origin folder,
        // AND the destination file name is the same as the original file name,
        // THEN we throw an error
        $info = pathinfo($this->originFilePath);
        $destinationFilePath = $this->getDestinationFilePath($info['dirname']);
        if (rtrim($this->originFilePath, DIRECTORY_SEPARATOR) === $destinationFilePath) {
            throw new TransformException($this, sprintf(
                "The origin file '%s' and the specified destination file '%s' cannot be the same.",
                $this->originFilePath,
                $destinationFilePath
            ));
        }

        return true;
    }

    /**
     * Apply the transformation.
     *
     * @return bool
     *
     * @throws TransformException
     */
    public function transform(): bool
    {
        if ($this->isValid()) {
            try {
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
                    'target' => $this->getDestinationFilePath($info['dirname']),
                    'overwrite' => true
                ]);
                $copyFilter->filter($this->originFilePath);

                return true;
            } catch (RuntimeException $runtimeException) {
                throw new TransformException($this, sprintf(
                    "An error occurred while copying file '%s' to '%s'. Error message is: '%s'",
                    $this->originFilePath,
                    $this->destinationFolder,
                    $runtimeException->getMessage()
                ));
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
    protected function getDestinationFilePath(string $defaultDestinationFolder = ""): string
    {
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
}
