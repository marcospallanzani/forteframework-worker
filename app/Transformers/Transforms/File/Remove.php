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

namespace Forte\Api\Generator\Transformers\Transforms\File;

use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Helpers\FileParser;
use Forte\Api\Generator\Transformers\Transforms\AbstractTransform;

/**
 * Class Remove.
 *
 * @package Forte\Api\Generator\Transformers\Transforms\File
 */
class Remove extends AbstractTransform
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
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if this Remove instance was well configured;
     * false otherwise.
     *
     * @throws TransformException
     */
    public function isValid(): bool
    {
        try {
            // The file path cannot be empty
            if (empty($this->filePath)) {
                $this->throwTransformException($this, "You must specify the file path.");
            }

            // The mode cannot be empty
            if (empty($this->mode)) {
                $this->throwTransformException($this, "You must specify the remove mode.");
            }

            // Check if the given mode is supported
            $modesConstants = self::getClassConstants('REMOVE_');
            if (!in_array($this->mode, $modesConstants)) {
                $this->throwTransformException(
                    $this,
                    "The specified mode '%s' is not supported. Supported modes are: '%s'",
                    $this->mode,
                    implode(',', $modesConstants)
                );
            }
        } catch (\ReflectionException $reflectionException) {
            $this->throwTransformException($this,
                "A general error occurred while retrieving the modes list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        return true;
    }

    /**
     * Apply the Remove action.
     *
     * @return bool True if the action implemented by this Remove
     * instance was successfully applied; false otherwise.
     *
     * @throws TransformException
     */
    protected function apply(): bool
    {
        switch ($this->mode) {
            case self::REMOVE_SINGLE_FILE:
                return $this->deleteFile($this->filePath);
                break;
            case self::REMOVE_DIRECTORY:
                return $this->deleteFolder($this->filePath);
                break;
            case self::REMOVE_FILE_PATTERN:
                return (bool) $this->deleteFilePattern($this->filePath);
                break;
        }

        return false;
    }

    /**
     * Set the file path to be removed with a specific delete mode.
     * Accepted modes are the class constants beginning by "REMOVE_".
     *
     * @param string $filePath The file path to be removed.
     * @param string $mode The delete mode (one of the class constants
     * that begin by "REMOVE_").
     *
     * @return Remove
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
     * @return Remove
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
     * @return Remove
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
     * @return Remove
     */
    public function removeDirectory(string $directoryPath): self
    {
        return $this->remove($directoryPath, self::REMOVE_DIRECTORY);
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
     * @param string $folderPath The folder path to be deleted.
     *
     * @return bool True if the given folder and its whole content
     * (sub-folders and files) were deleted; false otherwise.
     *
     * @throws TransformException The given path is neither a directory, nor a file.
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
        $this->throwTransformException(
            $this,
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
     * @throws TransformException If the given path is not a valid file path
     * (e.g. directory or file pattern).
     */
    protected function deleteFile(string $filePath): bool
    {
        if (!is_file($filePath)) {
            $this->throwTransformException($this, "'%s' is not a valid file path.", $this->filePath);
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
