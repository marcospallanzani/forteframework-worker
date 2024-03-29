<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Stdlib\Exceptions\GeneralException;
use Forte\Worker\Actions\AbstractFileAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Stdlib\Filters\Files\Copy as CopyFilter;

/**
 * Class CopyFile
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class CopyFile extends AbstractFileAction
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
        $this->copy($originalFilePath);
        $this->toFolder($destinationFolder);
        $this->withName($destinationFileName);
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
        $this->originFilePath = rtrim($originFilePath, DIRECTORY_SEPARATOR);

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
     * @return string The full destination file path (concat destination
     * folder and destination name).
     */
    public function getDestinationFilePath(): string
    {
        $info = pathinfo($this->originFilePath);
        $dirName        = array_key_exists('dirname', $info) ? $info['dirname'] : '';
        $fileName       = array_key_exists('filename', $info) ? $info['filename'] : '';
        $fileExtension  = array_key_exists('extension', $info) ? $info['extension'] : '';
        $fileBaseName   = array_key_exists('basename', $info) ? $info['basename'] : '';

        // We set the destination folder, if not specified
        if (empty($this->destinationFolder)) {
            // If no destination folder, we use the same folder as the file to be copied
            $targetFolder = $dirName;
        } else {
            $targetFolder = $this->destinationFolder;
        }

        // We set the destination file name, if not specified
        if (empty($this->destinationFileName)) {
            if ($targetFolder == $dirName) {
                $targetName = $fileName . "_COPY" . (empty($fileExtension) ? "" : ".$fileExtension");
            } else {
                $targetName = $fileBaseName;
            }
        } else {
            $targetName = $this->destinationFileName;
        }

        if ($targetFolder && $targetName) {
            return $targetFolder . DIRECTORY_SEPARATOR . $targetName;
        }
        return "";
    }

    /**
     * Set the path required by the CopyFile instance.
     *
     * @param string $path The path to be set.
     *
     * @return $this
     */
    public function path(string $path): CopyFile
    {
        return $this->copy($path);
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
        if ($this->originFilePath === $destinationFilePath) {
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

        $copyFilter = new CopyFilter([
            'target' => $this->getDestinationFilePath(),
            'overwrite' => true
        ]);
        $copyFilter->filter($this->originFilePath);

        return $actionResult->setResult(true);
    }
}
