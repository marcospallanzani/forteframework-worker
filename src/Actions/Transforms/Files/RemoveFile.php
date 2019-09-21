<?php
/**
 * This file is part of the ForteFramework package.
 *
 * Copyright (c) 2019  Marco Spallanzani <marco@forteframework.com>
 *
 *  For the full copyright and license information,
 *  please view the LICENSE file that was distributed
 *  with this source code.
 */

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractFileAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\WorkerException;

/**
 * Class RemoveFile.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class RemoveFile extends AbstractFileAction
{
    const REMOVE_SINGLE_FILE  = "remove_single_file";
    const REMOVE_FILE_PATTERN = "remove_file_pattern";
    const REMOVE_DIRECTORY    = "remove_directory";

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $mode;

    /**
     * RemoveFile constructor.
     *
     * @param string $filePath The file path to be removed.
     * @param string $mode The delete mode (one of the class constants
     * that begin by "REMOVE_").
     */
    public function __construct(string $filePath = "", string $mode = "")
    {
        parent::__construct();
        $this->remove($filePath, $mode);
    }

    /**
     * Set the file path to be removed with a specific delete mode.
     * Accepted modes are the class constants beginning by "REMOVE_".
     *
     * @param string $filePath The file path to be removed.
     * @param string $mode The delete mode (one of the class constants
     * that begin by "REMOVE_").
     *
     * @return RemoveFile
     */
    public function remove(string $filePath, string $mode): self
    {
        $this->filePath = $filePath;
        $this->mode = $mode;

        return $this;
    }

    /**
     * Set the file path to be removed. It also supports file patterns with the use
     * of the "*" character. (e.g. /path/to/test/*.txt)
     *
     * @param string $filePath The file path to be removed.
     *
     * @return RemoveFile
     */
    public function removeFile(string $filePath): self
    {
        return $this->remove($filePath, self::REMOVE_SINGLE_FILE);
    }

    /**
     * Set the file pattern to be removed. It supports the use
     * of the "*" character. (e.g. /path/to/test/*.txt).
     *
     * @param string $filePattern The file pattern to be removed.
     *
     * @return RemoveFile
     */
    public function removeFilePattern(string $filePattern): self
    {
        return $this->remove($filePattern, self::REMOVE_FILE_PATTERN);
    }

    /**
     * Set the file path to be removed.
     *
     * @param string $directoryPath The directory path to be removed.
     *
     * @return RemoveFile
     */
    public function removeDirectory(string $directoryPath): self
    {
        return $this->remove($directoryPath, self::REMOVE_DIRECTORY);
    }

    /**
     * Set the path required by the RemoveFile instance.
     *
     * @param string $path The path to be set.
     *
     * @return $this
     */
    public function path(string $path): RemoveFile
    {
        return $this->removeFile($path);
    }

    /**
     * Return a human-readable string representation of this Remove instance.
     *
     * @return string A human-readable string representation of this Remove instance.
     */
    public function stringify(): string
    {
        switch ($this->mode) {
            case self::REMOVE_SINGLE_FILE:
                return sprintf("Remove file '%s'.", $this->filePath);
                break;
            case self::REMOVE_DIRECTORY:
                return sprintf("Remove directory '%s'.", $this->filePath);
                break;
            case self::REMOVE_FILE_PATTERN:
                return sprintf("Remove files with pattern '%s'.", $this->filePath);
                break;
            default:
                return sprintf("Remove file '%s'.", $this->filePath);
        }
    }

    /**
     * Validate this CopyFile instance using its specific validation logic.
     * It returns true if this CopyFile instance is well configured, i.e. if:
     * - filePath is not be an empty string;
     * - mode is not be an empty string;
     * - mode is an accepted value (class constants starting with REMOVE_);
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The file path cannot be empty
        if (empty($this->filePath)) {
            $this->throwValidationException($this, "You must specify the file path.");
        }

        // The mode cannot be empty
        if (empty($this->mode)) {
            $this->throwValidationException($this, "You must specify the remove mode.");
        }

        // Check if the given mode is supported
        $modesConstants = self::getClassConstants('REMOVE_');
        if (!in_array($this->mode, $modesConstants)) {
            $this->throwValidationException(
                $this,
                "The specified mode '%s' is not supported. Supported modes are: '%s'",
                $this->mode,
                implode(',', $modesConstants)
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
        $removed = false;
        switch ($this->mode) {
            case self::REMOVE_SINGLE_FILE:
                $removed = $this->deleteFile($this->filePath);
                break;
            case self::REMOVE_DIRECTORY:
                $removed = $this->deleteFolder($this->filePath);
                break;
            case self::REMOVE_FILE_PATTERN:
                $removed = (bool) $this->deleteFilePattern($this->filePath);
                break;
        }

        return $actionResult->setResult($removed);
    }

    /**
     * @param string $folderPath The folder path to be deleted.
     *
     * @return bool True if the given folder and its whole content
     * (sub-folders and files) were deleted; false otherwise.
     *
     * @throws WorkerException The given path is neither a directory,
     * nor a file.
     */
    protected function deleteFolder(string $folderPath): bool
    {
        if (is_file($folderPath)) {
            // In case of a single file, we just delete it
            return @unlink($folderPath);
        } elseif (is_dir($folderPath)) {
            /**
             * If valid folder, we get a list of its files
             * and we reiterate through this method to delete
             * all files in all nested folders.
             */
            $scan = glob(rtrim($folderPath,'/').'/*');
            foreach($scan as $index => $path) {
                $this->deleteFolder($path);
            }
            return @rmdir($folderPath);
        }
        $this->throwWorkerException(
            "The given file path '%s' is neither a valid file, nor a directory, nor a file pattern.",
            $this->filePath
        );
    }

    /**
     * Delete the given file path.
     *
     * @param string $filePath The file path to be deleted.
     *
     * @return bool True if the file was deleted; false otherwise.
     *
     * @throws WorkerException If the given path is not a valid file path
     * (e.g. directory or file pattern).
     */
    protected function deleteFile(string $filePath): bool
    {
        if (!is_file($filePath)) {
            $this->throwWorkerException("'%s' is not a valid file path.", $this->filePath);
        }
        return @unlink($filePath);
    }

    /**
     * Delete a set of files for the given file pattern.
     * (e.g. /path/to/test/*.txt)
     *
     * @param string $filePattern The file pattern (containing the character '*').
     *
     * @return bool True if the files were all deleted; false if at least one file was not deleted.
     */
    protected function deleteFilePattern(string $filePattern): bool
    {
        foreach (glob($filePattern) as $file) {
            if (!unlink($file)) {
                return false;
            }
        }
        return true;
    }
}
