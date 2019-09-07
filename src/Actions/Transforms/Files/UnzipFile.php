<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Zend\Filter\Decompress;

/**
 * Class UnzipFile
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class UnzipFile extends AbstractAction
{
    /**
     * @var string
     */
    protected $zipFilePath;

    /**
     * @var string
     */
    protected $extractToPath;

    /**
     * Open the specified zip file.
     *
     * @param string $zipFilePath The zip file path.
     *
     * @return UnzipFile
     */
    public function open(string $zipFilePath): self
    {
        $this->zipFilePath = $zipFilePath;

        return $this;
    }

    /**
     * Extract the zip file to the specified destination path.
     *
     * @param string $extractToPath The destination extraction path.
     *
     * @return UnzipFile
     */
    public function extractTo(string $extractToPath): self
    {
        $this->extractToPath = $extractToPath;

        return $this;
    }

    /**
     * Validate the given action result. This method returns true if the
     * given ActionResult instance has a result value that is considered
     * as a positive case by this AbstractAction subclass instance.
     *
     * @param ActionResult $actionResult The ActionResult instance to be checked
     * with the specific validation logic of the current AbstractAction subclass
     * instance.
     *
     * @return bool True if the given ActionResult instance has a result value
     * that is considered as a positive case by this AbstractAction subclass
     * instance; false otherwise.
     */
    public function validateResult(ActionResult $actionResult): bool
    {
        // The ActionResult->result field should be set with a boolean
        // representing the last execution of the apply method.
        return (bool) $actionResult->getResult();
    }

    /**
     * Return a human-readable string representation of this Unzip instance.
     *
     * @return string A human-readable string representation of this Unzip instance.
     */
    public function stringify(): string
    {
        return sprintf("Unzip file '%s' to '%s'.", $this->zipFilePath, $this->extractToPath);
    }

    /**
     * Validate this UnzipFile instance using its specific validation logic.
     * It returns true if this UnzipFile instance is well configured, i.e. if:
     * - zipFilePath is not be an empty string;
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // The zip file path cannot be empty
        if (empty($this->zipFilePath)) {
            $this->throwActionException($this, "You must specify the zip file path.");
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
        // We check if the zip file exists
        $this->fileExists($this->zipFilePath);

        $info = pathinfo($this->zipFilePath);

        // We check if an extraction folder is set:
        // if empty, we use the folder of the set zip file.
        if (empty($this->extractToPath)) {
            $this->extractToPath = $info['dirname'];
        }

        try {
            $filter = new Decompress(array(
                'adapter' => 'Zip',
                'options' => array(
                    'target' => $this->extractToPath,
                )
            ));
            return $actionResult->setResult(
                (bool) $filter->filter($this->zipFilePath)
            );
        } catch (\Exception $exception) {
            /**
             * If file not unzipped (e.g. the user does not have the right to either
             * read the zip file or to write to the destination folder), we throw an
             * exception with a general error message.
             */
            $this->throwWorkerException(
                "Impossible to unzip the ZIP file %s. Reason: %s",
                $this->zipFilePath,
                $exception->getMessage()
            );
        }
    }
}
