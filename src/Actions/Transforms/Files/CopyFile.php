<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Filters\Files\Copy as CopyFilter;

/**
 * Class CopyFile
 *
 * @package Forte\Worker\Actions\Transforms\Files
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
     * CopyFile constructor.
     *
     * @param string $originalFilePath
     * @param string $destinationFolder
     * @param string $destinationFileName
     */
    public function __construct(
        string $originalFilePath = "",
        string $destinationFolder = "",
        string $destinationFileName = ""
    ) {
        parent::__construct();
        $this->originFilePath      = $originalFilePath;
        $this->destinationFolder   = $destinationFolder;
        $this->destinationFileName = $destinationFileName;
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

    /**
     * Validate this CopyFile instance using its specific validation logic.
     * It returns true if this CopyFile instance is well configured, i.e. if:
     * - originFilePath is not be an empty string;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The origin file path cannot be empty
        if (empty($this->originFilePath)) {
            $this->throwValidationException($this, "You must specify a file to be copied.");
        }

        // If no destination folder is specified, or it is the same as the origin folder,
        // AND the destination file name is the same as the original file name,
        // THEN we throw an error
        $destinationFilePath = $this->getDestinationFilePath();
        if (rtrim($this->originFilePath, DIRECTORY_SEPARATOR) === $destinationFilePath) {
            $this->throwValidationException(
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
     * @param ActionResult $actionResult The action result object to register
     * all failures and successful results.
     *
     * @return ActionResult The ActionResult instance with updated fields
     * regarding failures and result content.
     *
     * @throws \Exception
     */
    protected function apply(ActionResult $actionResult): ActionResult
    {
        // We check if the origin file exists
        $this->fileExists($this->originFilePath);

        $info = pathinfo($this->originFilePath);

        // We check if a destination folder is set:
        // if empty, we use the same origin file folder
        if (empty($this->destinationFolder)) {
            $this->destinationFolder = $info['dirname'];
        }

        // We check if the destination file name is set:
        // if empty, we use the origin file name with a "_Copy" suffix
        if (empty($this->destinationFileName)) {
            $this->destinationFileName = $info['filename'] . "_COPY." . $info['extension'];
        }

        $copyFilter = new CopyFilter([
            'target' => $this->getDestinationFilePath(),
            'overwrite' => true
        ]);
        $copyFilter->filter($this->originFilePath);

        return $actionResult->setResult(true);
    }
}
