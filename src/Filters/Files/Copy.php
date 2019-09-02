<?php

namespace Forte\Worker\Filters\Files;

use Zend\Filter\Exception\RuntimeException;
use Zend\Filter\File\Rename;

/**
 * Class Copy. This class copies a source file to a given destination.
 *
 * @package Forte\Worker\Filters\Files
 */
class Copy extends Rename
{
    /**
     * Copies the file $value to the new name set before.
     * Returns the file $value, removing all but digit characters
     *
     * @param  string|array $value Full path of file to change or $_FILES data array
     *
     * @throws RuntimeException
     *
     * @return string|array The new filename which has been set
     */
    public function filter($value)
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        $file = $this->getNewName($value, true);
        if (is_string($file)) {
            return $file;
        }

        $result = copy($file['source'], $file['target']);

        if ($result !== true) {
            throw new RuntimeException(
                sprintf(
                    "File '%s' could not be copied. " .
                    "An error occurred while processing the file.",
                    $value
                )
            );
        }

        return $file['target'];
    }
}
