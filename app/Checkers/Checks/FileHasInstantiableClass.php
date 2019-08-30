<?php

namespace Forte\Api\Generator\Checkers\Checks;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;

/**
 * Class FileHasInstantiableClass. This class checks if a given file
 * has an instantiable class.
 *
 * @package Forte\Api\Generator\Checkers\Checks
 */
class FileHasInstantiableClass extends FileExists
{
    /**
     * @var string
     */
    protected $class;

    /**
     * FileExists constructor.
     *
     * @param string $filePath The file path to check
     */
    public function __construct(string $filePath = "", string $class = "")
    {
        parent::__construct($filePath);
        $this->class    = $class;
    }

    /**
     * Run the check.
     *
     * @return bool Returns true if this AbstractCheck subclass
     * instance check has been successfully; false otherwise.
     *
     * @throws CheckException
     * @throws GeneratorException
     */
    public function check(): bool
    {

        if ($this->isValid()) {
            // We check if the specified file exists
            $this->checkFileExists($this->filePath);

            // Check to see whether the include declared the class
            $tokens = token_get_all(file_get_contents($this->filePath));

            $openTagFound = $classNameFound = false;
            $classDeclarationPos = 0;
            foreach ($tokens as $key => $token) {
                if (is_array($token)) {
                    if ($token[0] === T_OPEN_TAG ) {
                        $openTagFound = true;
                    } elseif ($token[0] === T_CLASS) {
                        $classDeclarationPos = $key;
                    }

                    if ($classDeclarationPos) {
                        // We increment the class declaration tag position to ignore the
                        // white spaces between the 'class' tag and the class name tag.
                        if ($token[0] === T_WHITESPACE) {
                            $classDeclarationPos++;
                        } elseif ($token[0] === T_STRING) {
                            if ($classDeclarationPos === ($key - 1)) {
                                // If the current token is the one right after the 'class_declaration' token
                                // (white spaces are ignored), then we check if the current token is a string
                                // and is equal to the expected class name.
                                if ($token[1] === $this->class) {
                                    $classNameFound = true;
                                }
                            }
                        }
                    }
                }

                if ($openTagFound && $classNameFound) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set the expected class to be searched in the configured file path.
     *
     * @param string $class The expected class
     *
     * @return FileHasInstantiableClass
     */
    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Return a string representation of this AbstractCheck subclass instance.
     *
     * @return string
     */
    public function stringify(): string
    {
        return sprintf(
            "Check if file '%s' has %s",
            $this->filePath,
            (empty($this->class) ? "an instatiable class." : "the class '" . $this->class . "'")
        );
    }
}
