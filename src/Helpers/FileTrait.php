<?php

namespace Forte\Worker\Helpers;

use Forte\Worker\Exceptions\WorkerException;
use Zend\Validator\File\NotExists;

/**
 * Trait FileTrait. Trait with helper methods for all file access actions.
 *
 * @package Forte\Worker\Helpers
 */
trait FileTrait
{
    /**
     * Checks if the given file path points to an existing file.
     *
     * @param string $filePath The file path to be checked
     * @param bool $raiseError Whether an exception should be thrown if
     * the file does not exist.
     *
     * @return bool Returns true if the given file path points to an
     * existing file; false otherwise.
     *
     * @throws WorkerException
     */
    public function fileExists(string $filePath, bool $raiseError = true): bool
    {
        // We check if the given file exists
        $notExists = new NotExists();
        if ($notExists->isValid($filePath)) {
            if ($raiseError) {
                throw new WorkerException(sprintf(
                    "The file '%s' does not exist.",
                    $filePath
                ));
            }
            return false;
        }

        return true;
    }
}