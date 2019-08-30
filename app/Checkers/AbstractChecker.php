<?php

namespace Forte\Api\Generator\Checkers;

use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Checkers\Checks\AbstractCheck;

/**
 * Class AbstractChecker. A base class for all checker implementations.
 *
 * @package Forte\Api\Generator\Checkers
 */
class AbstractChecker
{
    /**
     * Checks to verify.
     *
     * @var array An array of AbstractCheck subclass instances.
     */
    protected $checks = [];

    /**
     * Get all the checks to be verified.
     *
     * @return array An array of AbstractCheck subclass instances.
     */
    public function getChecks(): array
    {
        return $this->checks;
    }

    /**
     * Add a check to be verified.
     *
     * @param AbstractCheck $check
     */
    public function addCheck(AbstractCheck $check)
    {
        $this->checks[] = $check;
    }

    /**
     * Verify all configured checks in the given sequence.
     * This method returns a list of AbstractCheck subclass
     * that failed  or that did not execute correctly.
     *
     * @return array A list of AbstractCheck subclass instances
     * that executed correctly, but failed.
     */
    public function verifyChecks(): array
    {
        $failedChecks = array();
        foreach ($this->checks as $check) {
            try {
                if ($check instanceof AbstractCheck && !$check->run()) {
                    $failedChecks[] = $check;
                }
            } catch (GeneratorException $generatorException) {
                $failedChecks[] = $generatorException->getMessage();
            }
        }
        return $failedChecks;
    }
}
