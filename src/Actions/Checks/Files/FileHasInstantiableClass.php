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

namespace Forte\Worker\Actions\Checks\Files;

use Forte\Worker\Actions\ActionResult;

/**
 * Class FileHasInstantiableClass. This class checks if a given file
 * has an instantiable class.
 *
 * @package Forte\Worker\Actions\Checks\Files
 */
class FileHasInstantiableClass extends FileExists
{
    /**
     * @var string
     */
    protected $class;

    /**
     * FileHasInstantiableClass constructor.
     *
     * @param string $filePath The file path to check.
     * @param string $class The class name to search for.
     */
    public function __construct(string $filePath = "", string $class = "")
    {
        parent::__construct($filePath);
        $this->class = $class;
    }

    /**
     * Set the expected class to be searched in the configured file path.
     *
     * @param string $class The expected class
     *
     * @return FileHasInstantiableClass
     */
    public function hasClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Return a human-readable string representation of this
     * FileHasInstantiableClass instance.
     *
     * @return string A human-readable string representation
     * of this FileHasInstantiableClass instance.
     */
    public function stringify(): string
    {
        return sprintf(
            "Check if file '%s' has %s",
            $this->filePath,
            (empty($this->class) ? "an instatiable class." : "the class '" . $this->class . "'.")
        );
    }

    /**
     * Run the check. This method checks if the specified file has an
     * instantiable class. If the additional parameter "class" is specified,
     * then this method will also check if the class found in the file is
     * equal to the one that was given as a parameter.
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
        // We check if the specified file exists
        $this->fileExists($this->filePath);

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
                            /**
                             * If the current token is the one right after the 'class_declaration' token
                             * (white spaces are ignored), then we check the class condition:
                             * - if a class name is specified, we check if the current token is equal to
                             *   the expected class name;
                             * - if no class name is given, we check if the current token is a non-empty
                             *   string;
                             */
                            if ((empty($this->class) && !empty($token[1]))
                                || (!empty($this->class) && $token[1] === $this->class)
                            ) {
                                $classNameFound = true;
                            }
                        }
                    }
                }
            }
            if ($openTagFound && $classNameFound) {
                return $actionResult->setResult(true);
            }
        }

        return $actionResult->setResult(false);
    }
}
