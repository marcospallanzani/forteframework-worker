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

namespace Forte\Worker\Transformers\Transforms\Files;

use Forte\Worker\Checkers\Checks\Strings\VerifyString;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\StringParser;
use Forte\Worker\Helpers\ThrowErrorsTrait;
use Forte\Worker\Transformers\Transforms\AbstractTransform;

/**
 * Class ModifyFile. This class is used to modify the content of a given
 * file. The content will be parsed line by line and the configured changes
 * will be applied.
 *
 * @package Forte\Worker\Transformers\Transforms\Files
 */
class ModifyFile extends AbstractTransform
{
    use ClassAccessTrait, ThrowErrorsTrait;

    /**
     * Supported actions.
     */
    const MODIFY_FILE_REPLACE_IN_LINE       = "replace_content_in_line";
    const MODIFY_FILE_REPLACE_LINE          = "replace_line";
    const MODIFY_FILE_REMOVE_IN_LINE        = "remove_content_in_line";
    const MODIFY_FILE_REMOVE_LINE           = "remove_line";
    const MODIFY_FILE_APPEND_TO_LINE        = "append_content_to_line";
    const MODIFY_FILE_APPEND_TEMPLATE       = "append_template";
    const MODIFY_FILE_REPLACE_WITH_TEMPLATE = "replace_with_template";

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var array
     */
    protected $actions = [];

    /**
     * ModifyFile constructor.
     *
     * This class performs different actions to modify a given file. The available
     * actions are:
     * 1. "replace_content_in_line": replace a content with a given replace content
     *    in a line that matched the search condition;
     * 2. "replace_line": replace a whole line, that matched the search condition,
     *    with a given replace content;
     * 3. "remove_content_in_line": remove a matched content in a line that matched
     *    the search condition;
     * 4. "remove_line": remove a whole line that matched the searched content;
     * 5. "append_content_to_line": append a content to a line that matched the
     *    search condition;
     * 6. "append_template": append a given template (replace value) to a line that
     *    matched the search condition;
     * 7. "replace_with_template": replace a line, that matched the searc condition,
     *   with a given template (replace value)
     *
     * These actions can be used with some conditional parameters, to
     * determine whether or not that action should be applied. The available
     * conditions are described by the object VerifyString.
     *
     * @param string $filePath The file path of the file to be modified.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Replace the search value with the replace value in each line of the specified
     * file, that starts with the given condition value.
     *
     * @param mixed $conditionValue
     * @param mixed $searchValue
     * @param mixed $replaceValue
     *
     * @return ModifyFile
     */
    public function replaceValueIfLineStartsWith($conditionValue, $searchValue, $replaceValue): self
    {
        return $this->addAction(
            self::MODIFY_FILE_REPLACE_IN_LINE,
            VerifyString::CONDITION_STARTS_WITH,
            $conditionValue,
            $replaceValue,
            $searchValue
        );
    }

    /**
     * Replace each line, of the specified file, that starts with the given condition
     * value with the given replace value.
     *
     * @param mixed $conditionValue
     * @param mixed $replaceValue
     *
     * @return ModifyFile
     */
    public function replaceLineIfLineStartsWith($conditionValue, $replaceValue): self
    {
        return $this->addAction(
            self::MODIFY_FILE_REPLACE_LINE,
            VerifyString::CONDITION_STARTS_WITH,
            $conditionValue,
            $replaceValue
        );
    }

    /**
     * Remove the search value with the replace value in each line of the specified
     * file, that starts with the given condition value.
     *
     * @param mixed $conditionValue The value to be used to run the action condition.
     * @param mixed $searchValue The value to search for in the original string and that
     * will be modified by the action specific modification (e.g. replace searchValue
     * with replaceValue).
     *
     * @return ModifyFile
     */
    public function removeValueIfLineStartsWith($conditionValue, $searchValue): self
    {
        return $this->addAction(
            self::MODIFY_FILE_REMOVE_IN_LINE,
            VerifyString::CONDITION_STARTS_WITH,
            $conditionValue,
            '',
            $searchValue
        );
    }

    /**
     * Remove each line, of the specified file, that starts with the given condition
     * value with the given replace value.
     *
     * @param mixed $conditionValue The condition value.
     *
     * @return ModifyFile
     */
    public function removeLineIfLineStartsWith($conditionValue): self
    {
        return $this->addAction(
            self::MODIFY_FILE_REMOVE_LINE,
            VerifyString::CONDITION_STARTS_WITH,
            $conditionValue
        );
    }

    /**
     * Replace each line, of the specified file, that is equal to the given condition
     * value, with the given template.
     *
     * @param string $templatePath The template path.
     * @param mixed $conditionValue The condition value.
     *
     * @return ModifyFile
     */
    public function replaceWithTemplateIfLineEqualTo(string $templatePath, $conditionValue): self
    {
        return $this->addAction(
            self::MODIFY_FILE_REPLACE_WITH_TEMPLATE,
            VerifyString::CONDITION_EQUAL_TO,
            $conditionValue,
            $templatePath
        );
    }

    /**
     * Add the given template to each line, of the specified file, that is equal to the given
     * condition value.
     *
     * @param string $templatePath The template path.
     * @param mixed $conditionValue The condition value.
     *
     * @return ModifyFile
     */
    public function addTemplateIfLineEqualTo(string $templatePath, $conditionValue): self
    {
        return $this->addAction(
            self::MODIFY_FILE_APPEND_TEMPLATE,
            VerifyString::CONDITION_EQUAL_TO,
            $conditionValue,
            $templatePath
        );
    }

    /**
     * Add a modification action to be applied to the specified file.
     *
     * @param string $actionType The action type (possible values are class constants
     * starting with prefix MODIFY_FILE_).
     * @param string $conditionType The condition type (possible values are VerifyString
     * constants starting with prefix CONDITION_).
     * @param mixed $conditionValue The value to be used to run the action condition.
     * @param mixed $replaceValue The value to be used to perform the action specific
     * modification (e.g. replace, add value).
     * @param mixed $searchValue The value to search for in the original string and that
     * will be modified by the action specific modification (e.g. replace searchValue
     * with replaceValue).
     *
     * @return ModifyFile
     */
    public function addAction(
        string $actionType,
        string $conditionType,
        $conditionValue,
        $replaceValue = "",
        $searchValue = ""
    ): self
    {
        $this->actions[] = [
            "action"    => $actionType,
            'search'    => $searchValue,
            "value"     => $replaceValue,
            "condition" => new VerifyString($conditionType, $conditionValue)
        ];

        return $this;
    }

    /**
     * Whether this instance is in a valid state or not.
     *
     * @return bool True if the implementing class instance
     * was well configured; false otherwise.
     *
     * @throws WorkerException If the implementing class
     * instance was not well configured.
     */
    public function isValid(): bool
    {
        try {
            if (empty($this->filePath)) {
                $this->throwTransformException(
                    $this,
                    "You need to specify the 'filePath' for the following transformation: '%s'.",
                    $this
                );
            }

            // If no action is specified OR an unsupported action is given, then we throw an error.
            $modifyConstants = $this->getSupportedActions();
            $wrongActionsAndConditions = array();
            foreach ($this->actions as $action) {
                if (!in_array($action['action'], $modifyConstants)) {
                    $wrongActionsAndConditions[] = sprintf(
                        "The action '%s' is not supported. Impacted transformation is: '%s'. Supported actions are: '%s'.",
                        $action['action'],
                        $this,
                        implode(',', $modifyConstants)
                    );
                }

                $condition = $action['condition'];
                if (!$condition instanceof VerifyString) {
                    $wrongActionsAndConditions[] = sprintf(
                        "The action '%s' has a non-recognized condition. Impacted transformation is: '%s'.",
                        $action['action'],
                        $this
                    );
                }

                try {
                    $condition->isValid();
                } catch (WorkerException $workerException) {
                    $wrongActionsAndConditions[] = sprintf(
                        "The condition '%s' is not valid. Error message is: '%s'.",
                        $condition,
                        $workerException->getMessage()
                    );
                }
            }

            if ($wrongActionsAndConditions) {
                $message = "";
                foreach ($wrongActionsAndConditions as $key => $errorMessage) {
                    $message .= "$key. $errorMessage;";
                }
                $this->throwTransformException(
                    $this,
                    "This modify-file transformation is not well configured: '%s'. Error message is: '%s'.",
                    $this,
                    $message
                );
            }
            return true;

        } catch (\ReflectionException $reflectionException) {
            $this->throwWorkerException(
                "A general error occurred while retrieving the actions list. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }

        return false;
    }

    /**
     * Apply the sub-class transformation action.
     *
     * @return bool True if the action implemented by this AbstractTransform
     * subclass instance was successfully applied; false otherwise.
     *
     * @throws WorkerException
     */
    protected function apply(): bool
    {
        // We check if the origin file exists
        $this->checkFileExists($this->filePath);

        // We open the file. we read it line by line and we modify each line if the condition is met
        $fileHandler = fopen($this->filePath, "r");
        $modifiedContent = [];
        while(! feof($fileHandler))  {
            $line = fgets($fileHandler);
            foreach ($this->actions as $key => $action) {
                // We first check if the condition is met
                $condition = $action['condition'];
                if ($condition instanceof VerifyString && $condition->setContent(trim($line, PHP_EOL))->run()) {
                    // We have to apply the configured change
                    switch ($action['action']) {
                        case self::MODIFY_FILE_APPEND_TO_LINE:
                            if (StringParser::endsWith($line, PHP_EOL)) {
                                $line = trim($line, PHP_EOL) . $action['value'] . PHP_EOL;
                            } else {
                                $line .= $action['value'];
                            }
                            break;
                        case self::MODIFY_FILE_REMOVE_IN_LINE:
                            $line = str_replace($action['search'], "", $line);
                            break;
                        case self::MODIFY_FILE_REMOVE_LINE:
                            if (StringParser::endsWith($line, PHP_EOL)) {
                                $line = PHP_EOL;
                            } else {
                                $line = null;
                            }
                            break;
                        case self::MODIFY_FILE_REPLACE_IN_LINE:
                            $line = str_replace($action['search'], $action['value'], $line);
                            break;
                        case self::MODIFY_FILE_REPLACE_LINE:
                            $newLine = $action['value'];
                            if (StringParser::endsWith($line, PHP_EOL)) {
                                $newLine .= PHP_EOL;
                            }
                            $line = $newLine;
                            break;
                        case self::MODIFY_FILE_APPEND_TEMPLATE:
                            // In this case, the replace value, should be a valid file name that we want to append
                            // at the end of the current line
                            $line .= $this->getFileContent($action['value']);
                            break;
                        case self::MODIFY_FILE_REPLACE_WITH_TEMPLATE:
                            // In this case, the replace value, should be a valid file name that we want to replace
                            // to the current line
                            $line = $this->getFileContent($action['value']);
                            break;
                    }
                }
            }
            if (!is_null($line)) {
                $modifiedContent[] = $line;
            }
        }
        fclose($fileHandler);

        // We write the modified content line by line to the same file
        $fileHandler = fopen($this->filePath, 'w+') or die("Can't open file.");
        foreach ($modifiedContent as $line) {
            fwrite($fileHandler, $line);
        }
        fclose($fileHandler);

        return true;
    }

    /**
     * Return a human-readable string representation of this
     * ModifyFile instance.
     *
     * @return string A human-readable string representation
     * of this ModifyFile instance.
     */
    public function stringify(): string
    {
        $message = "Apply the following transformations to the specified file '" . $this->filePath . "': " . PHP_EOL;
        if (!$this->actions) {
            $message .= "No transformations configured." . PHP_EOL;
        } else {
            foreach ($this->actions as $key => $action) {
                switch ($action['action']) {
                    case self::MODIFY_FILE_APPEND_TO_LINE:
                        $message .= sprintf(
                                "%d. Append content '%s' to each line that meets the following condition: '%s';",
                                $key,
                                $action['value'],
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                    case self::MODIFY_FILE_REMOVE_IN_LINE:
                        $message .= sprintf(
                                "%d. Remove content '%s' in each line that meets the following condition: '%s';",
                                $key,
                                $action['search'],
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                    case self::MODIFY_FILE_REMOVE_LINE:
                        $message .= sprintf(
                                "%d. Remove each line that meets the following condition: '%s';",
                                $key,
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                    case self::MODIFY_FILE_REPLACE_IN_LINE:
                        $message .= sprintf(
                                "%d. Replace content '%s' with '%s' in each line that meets the following condition: '%s';",
                                $key,
                                $action['search'],
                                $action['value'],
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                    case self::MODIFY_FILE_REPLACE_LINE:
                        $message .= sprintf(
                                "%d. Replace each line that meets the following condition with '%s': '%s';",
                                $key,
                                $action['value'],
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                    case self::MODIFY_FILE_APPEND_TEMPLATE:
                        $message .= sprintf(
                                "%d. Append template '%s' to each line that meets the following condition: '%s';",
                                $key,
                                (string) $action['value'],
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                    case self::MODIFY_FILE_REPLACE_WITH_TEMPLATE:
                        $message .= sprintf(
                                "%d. Replace each line that meets the following condition, with template '%s': '%s';",
                                $key,
                                (string) $action['value'],
                                (string) $action['condition']
                            ) . PHP_EOL;
                        break;
                }
            }
        }

        return $message;
    }

    /**
     * Return a list of configured actions.
     *
     * @return array
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Return an associative array of all available actions. Possible values
     * are class constants, that begin by "MODIFY_FILE_".
     *
     * @return array Actions list.
     *
     * @throws WorkerException
     */
    public function getSupportedActions(): array
    {
        try {
            return self::getClassConstants('MODIFY_FILE_');
        } catch (\ReflectionException $reflectionException) {
            $this->throwWorkerException(
                "An error occurred while retrieving the list of supported actions. Error message is: '%s'.",
                $reflectionException->getMessage()
            );
        }
    }

    /**
     * Read the given file and return its content as a string.
     *
     * @param string $filePath The file path to read.
     *
     * @return string The file content.
     *
     * @throws WorkerException
     */
    protected function getFileContent(string $filePath): string
    {
        $this->checkFileExists($filePath);

        return file_get_contents($filePath);
    }
}
