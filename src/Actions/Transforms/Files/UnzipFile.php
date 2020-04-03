<?php

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractFileAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Exceptions\ConfigurationException;
use Laminas\Filter\Decompress;

/**
 * Class UnzipFile
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class UnzipFile extends AbstractFileAction
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
     * UnzipFile constructor.
     *
     * @param string zipFilePath The file to decompress.
     */
    public function __construct(string $zipFilePath = "")
    {
        parent::__construct();
        $this->zipFilePath = $zipFilePath;
    }

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
     * Extract the zip file to the specified destination path. If the given extractToPath
     * does not exist, the first parent folder of the given directory will be used.
     *
     * @param string $extractToPath The destination extraction path.
     * @param bool   $force         Whether or not this action should force the creation of
     *                              the specified extract-to path.
     *
     * @return UnzipFile
     *
     * @throws ConfigurationException
     */
    public function extractTo(string $extractToPath, bool $force = false): self
    {
        $this->extractToPath = $extractToPath;

        // If the extract path does not exist, and we are in the force mode,
        // we need to check if it exists; if it does not, then we create the
        // given extract-to path on the file system
        if ($force) {
            $this->addBeforeAction(
                ActionFactory::createIfStatement(
                    null,
                    [
                        [
                            ActionFactory::createDirectoryDoesNotExist($extractToPath),
                            ActionFactory::createMakeDirectory($extractToPath)
                        ],
                    ]
                )
            );
        }

        return $this;
    }

    /**
     * Set the path required by the UnzipFile instance.
     *
     * @param string $path The path to be set.
     *
     * @return $this
     */
    public function path(string $path): UnzipFile
    {
        return $this->open($path);
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
            $this->throwValidationException($this, "You must specify the zip file path.");
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
