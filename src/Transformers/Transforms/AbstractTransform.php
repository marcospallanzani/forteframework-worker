<?php

namespace Forte\Worker\Transformers\Transforms;

use Forte\Worker\Actions\ValidActionInterface;
use Forte\Worker\Exceptions\CheckException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\FileTrait;
use Forte\Worker\Helpers\ThrowErrorsTrait;
use Forte\Worker\Checkers\Checks\AbstractCheck;

/**
 * Class AbstractTransform
 *
 * @package Forte\Worker\Transformers\Transforms
 */
abstract class AbstractTransform implements ValidActionInterface
{
    use ClassAccessTrait, FileTrait, ThrowErrorsTrait;

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
     * Apply the sub-class transformation action.
     *
     * @return bool True if the action implemented by this AbstractTransform
     * subclass instance was successfully applied; false otherwise.
     *
     * @throws WorkerException
     */
    protected abstract function apply(): bool;

    /**
     * Run the sub-class transformation action with pre-validation,
     * pre- and post-transformation checks.
     *
     * @return bool True if this AbstractTransform subclass
     * instance has been successfully applied; false otherwise.
     *
     * @throws WorkerException
     */
    public function run(): bool
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
     * Add the given AbstractCheck subclass instance to the list of
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
     * Add the given AbstractCheck subclass instance to the list of
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
     * @return array List of failed pre-transform checks.
     */
    protected function runBeforeChecks(): array
    {
        $failedChecks = array();
        foreach ($this->beforeChecks as $check) {
            try {
                if ($check instanceof AbstractCheck && !$check->run()) {
                    $failedChecks[] = new CheckException($check, "Check failed.");
                }
            } catch (WorkerException $generatorException) {
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
     * @return array List of failed post-transform checks.
     */
    protected function runAfterChecks(): array
    {
        $failedChecks = array();
        foreach ($this->afterChecks as $check) {
            try {
                if ($check instanceof AbstractCheck && !$check->run()) {
                    $failedChecks[] = new CheckException($check, "Check failed.");
                }
            } catch (WorkerException $generatorException) {
                $failedChecks[] = new CheckException($check,
                    sprintf("Check failed with error '%s'.", $generatorException->getMessage())
                );
            }
        }

        return $failedChecks;
    }

    /**
     * Run the configured pre-transform checks and report all the occurred errors.
     *
     * @param bool $throwException Whether we should throw an exception for the
     * failed checks OR return a string representation of them.
     *
     * @return string A string representation of the failed checks, in case the
     * thrownException flag is true.
     *
     * @throws WorkerException
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
                $this->throwWorkerException($message);
            }
        }

        return $message;
    }

    /**
     * Run the configured post-transform checks and report all the occurred errors.
     *
     * @param bool $throwException Whether we should throw an exception for the failed
     * checks OR return a string representation of them.
     *
     * @return string A string representation of the failed checks, in case the
     * thrownException flag is true.
     *
     * @throws WorkerException
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
                $this->throwWorkerException($message);
            }
        }

        return $message;
    }

    /**
     * Return a string representation of this AbstractTransform subclass instance.
     *
     * @return false|string A string representation of this AbstractTransform
     * subclass instance.
     */
    public function __toString()
    {
        return static::stringify();
    }
}
