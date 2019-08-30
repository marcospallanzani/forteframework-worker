<?php

namespace Forte\Api\Generator\Transformers\Transforms;

use Forte\Api\Generator\Exceptions\CheckException;
use Forte\Api\Generator\Exceptions\GeneratorException;
use Forte\Api\Generator\Exceptions\TransformException;
use Forte\Api\Generator\Helpers\ClassAccessTrait;
use Forte\Api\Generator\Helpers\FileTrait;
use Forte\Api\Generator\Helpers\ThrowErrors;
use Forte\Api\Generator\Checkers\Checks\AbstractCheck;

/**
 * Class AbstractTransform
 *
 * @package Forte\Api\Generator\Transformers\Transforms
 */
abstract class AbstractTransform
{
    use ClassAccessTrait, FileTrait, ThrowErrors;

    /**
     * List of AbstractCheck subclass instances to be run before the transform method.
     *
     * @var array
     */
    protected $beforeChecks = array();

    /**
     * List of AbstractCheck subclass instances to be run after the transform method.
     *
     * @var array
     */
    protected $afterChecks = array();

    /**
     * Get whether this instance is in a valid state or not.
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance is correctly configured; false otherwise.
     *
     * @throws GeneratorException
     */
    public abstract function isValid(): bool;

    /**
     * Apply the sub-class transformation action.
     *
     * @return bool Returns true if the transform action implemented by
     * this AbstractTransform subclass instance has been successfully
     * applied; false otherwise.
     *
     * @throws GeneratorException
     */
    protected abstract function apply(): bool;

    /**
     * Apply the sub-class transformation action with pre-validation,
     * pre- and post-transformation checks
     *
     * @return bool Returns true if this AbstractTransform subclass
     * instance has been successfully applied; false otherwise.
     *
     * @throws GeneratorException
     */
    public function transform(): bool
    {
        $result = false;

        if ($this->isValid()) {

            // We run the pre-transform checks
            $this->runAndReportBeforeChecks(true);

            $result = $this->apply();

            // We run the post-transform checks
            $this->runAndReportAfterChecks(true);
        }

        return $result;
    }

    /**
     * Adds the given AbstractCheck subclass instance to the list of
     * pre-transform checks.
     *
     * @param AbstractCheck $check The AbstractCheck subclass instance
     * to be added to the list of pre-transform checks.
     *
     * @return AbstractTransform
     */
    public function addBeforeCheck(AbstractCheck $check): self
    {
        $this->beforeChecks[] = $check;

        return $this;
    }

    /**
     * Adds the given AbstractCheck subclass instance to the list of
     * post-transform checks.
     *
     * @param AbstractCheck $check The AbstractCheck subclass instance
     * to be added to the list of post-transform checks.
     *
     * @return AbstractTransform
     */
    public function addAfterCheck(AbstractCheck $check): self
    {
        $this->afterChecks[] = $check;

        return $this;
    }

    /**
     * Run the pre-transform checks and return a list failed
     * AbstractCheck instances.
     *
     * @return array Array with failed pre-transform checks.
     */
    protected function runBeforeChecks(): array
    {
        $failedChecks = array();
        foreach ($this->beforeChecks as $check) {
            try {
                if ($check instanceof AbstractCheck && !$check->check()) {
                    $failedChecks[] = new CheckException($check, "Check failed.");
                }
            } catch (GeneratorException $generatorException) {
                $failedChecks[] = new CheckException($check,
                    sprintf("Check failed with error '%s'.", $generatorException->getMessage())
                );
            }
        }
        return $failedChecks;
    }

    /**
     * Run the post-transform checks and return a list failed
     * AbstractCheck instances.
     *
     * @return array Array with failed post-transform checks.
     */
    protected function runAfterChecks(): array
    {
        $failedChecks = array();
        foreach ($this->afterChecks as $check) {
            try {
                if ($check instanceof AbstractCheck && !$check->check()) {
                    $failedChecks[] = new CheckException($check, "Check failed.");
                }
            } catch (GeneratorException $generatorException) {
                $failedChecks[] = new CheckException($check,
                    sprintf("Check failed with error '%s'.", $generatorException->getMessage())
                );
            }
        }

        return $failedChecks;
    }

    /**
     * Runs the configured pre-transform checks and report all
     * the occurred errors.
     *
     * @param bool $throwException Whether we should throw an exception for the failed checks
     * OR return a string representation of them.
     *
     * @return string Returns a string representation of the failed checks,
     * in case the thrownException flag is true.
     *
     * @throws GeneratorException
     */
    protected function runAndReportBeforeChecks($throwException = false): string
    {
        // We run the pre-transform checks
        $failedChecks = $this->runBeforeChecks();

        $message = "";
        if ($failedChecks) {
            $message = "The following pre-checks have failed: ";
            foreach ($failedChecks as $failedCheck) {
                if ($failedCheck instanceof CheckException) {
                    $message .= sprintf(
                        "%s. FAILED CHECKS INFO: %s. |||| ",
                        $failedCheck->getCheck(),
                        $failedCheck->getMessage()
                    );
                }
            }

            if ($throwException) {
                $this->throwGeneratorException($message);
            }
        }

        return $message;
    }

    /**
     * Runs the configured post-transform checks and report all
     * the occurred errors.
     *
     * @param bool $throwException Whether we should throw an exception for the failed checks
     * OR return a string representation of them.
     *
     * @return string Returns a string representation of the failed checks,
     * in case the thrownException flag is true.
     *
     * @throws GeneratorException
     */
    protected function runAndReportAfterChecks($throwException = false): string
    {
        // We run the post-transform checks
        $failedChecks = $this->runAfterChecks();

        $message = "";
        if ($failedChecks) {
            $message = "The following post-checks have failed: ";
            foreach ($failedChecks as $failedCheck) {
                if ($failedCheck instanceof CheckException) {
                    $message .= sprintf(
                        "%s. FAILED CHECKS INFO: %s. |||| ",
                        $failedCheck->getCheck(),
                        $failedCheck->getMessage()
                    );
                }
            }

            if ($throwException) {
                $this->throwGeneratorException($message);
            }
        }

        return $message;
    }

    /**
     * Returns a string representation of this AbstractTransform subclass instance.
     *
     * @return string
     */
    public abstract function stringify(): string;

    /**
     * Returns a string representation of this AbstractTransform subclass instance.
     *
     * @return false|string
     */
    public function __toString()
    {
        return static::stringify();
    }
}
