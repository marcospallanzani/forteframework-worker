<?php

namespace Forte\Worker\Transformers\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Filters\Files\Copy as CopyFilter;
use Zend\Filter\Exception\RuntimeException;

/**
 * Class CopyFile
 *
 * @package Forte\Worker\Transformers\Transforms\Files
 */
class CopyFile extends AbstractAction
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
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if this Copy instance was well configured;
     * false otherwise.
     *
     * @throws ActionException
     */
    public function isValid(): bool
    {
        // The origin file path cannot be empty
        if (empty($this->originFilePath)) {
            $this->throwActionException($this, "You must specify a file to be copied.");
        }

        // If no destination folder is specified, or it is the same as the origin folder,
        // AND the destination file name is the same as the original file name,
        // THEN we throw an error
        $destinationFilePath = $this->getDestinationFilePath();
        if (rtrim($this->originFilePath, DIRECTORY_SEPARATOR) === $destinationFilePath) {
            $this->throwActionException(
                $this,
                "The origin file '%s' and the specified destination file '%s' cannot be the same.",
                $this->originFilePath,
                $destinationFilePath
            );
        }

        return true;
    }

    /**
     * Apply the sub-class transformation action.
     *
     * @return bool True if the transform action implemented by this
     * Copy instance was successfully applied; false otherwise.
     *
     * @throws ActionException
     */
    protected function apply(): bool
    {
        try {
            // We check if the origin file exists
            $this->checkFileExists($this->originFilePath);

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

            return true;
        } catch (RuntimeException $runtimeException) {
            $this->throwActionException(
                $this,
                "An error occurred while copying file '%s' to '%s'. Error message is: '%s'",
                $this->originFilePath,
                $this->destinationFolder,
                $runtimeException->getMessage()
            );
        }

        return false;
    }

    /**
     * Set the origin file path.
     *
     * @param string $originFilePath The full file path to be copied
     *
     * @return CopyFile
     */
    public function copy(string $originFilePath): self
    {
        $this->originFilePath = $originFilePath;

        return $this;
    }

    /**
     * Set the destination folder.
     *
     * @param string $destinationFolder The full destination directory path
     *
     * @return CopyFile
     */
    public function toFolder(string $destinationFolder): self
    {
        $this->destinationFolder = rtrim($destinationFolder, DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * Set the destination file name.
     *
     * @param string $fileName The destination file name
     *
     * @return CopyFile
     */
    public function withName(string $fileName): self
    {
        $this->destinationFileName = trim($fileName, DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * Create the full destination file path as a concatenation of the
     * destination folder and the destination name.
     *
     * @param string $defaultDestinationFolder Default destination folder
     * to be used, if the class destination folder is empty.
     *
     * @return string The full destination file path (concat destination
     * folder and destination name).
     */
    public function getDestinationFilePath(string $defaultDestinationFolder = ""): string
    {
        // If no default destination folder is specified, we try to use
        // the default class destination folder
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
     * Return the default destination folder (i.e. same folder as
     * the original file path).
     *
     * @return string The default destination folder.
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
     * Return a human-readable string representation of this Copy instance.
     *
     * @return string A human-readable string representation of this Copy instance.
     */
    public function stringify(): string
    {
        return sprintf("Copy file '%s' to '%s'.", $this->originFilePath, $this->getDestinationFilePath());
    }
}
