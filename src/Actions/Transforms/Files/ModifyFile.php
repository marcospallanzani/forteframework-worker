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

namespace Forte\Worker\Actions\Transforms\Files;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\NestedActionCallbackInterface;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ClassAccessTrait;
use Forte\Worker\Helpers\StringParser;
use Forte\Worker\Helpers\ThrowErrorsTrait;

/**
 * Class ModifyFile. This class is used to modify the content of a given
 * file. The content will be parsed line by line and the configured changes
 * will be applied.
 *
 * @package Forte\Worker\Actions\Transforms\Files
 */
class ModifyFile extends AbstractAction implements NestedActionCallbackInterface
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
    public function __construct(string $filePath = "")
    {
        parent::__construct();
        $this->filePath = $filePath;
    }

    /**
     * Set the file path to be modified.
     *
     * @param string $filePath The file to modify.
     *
     * @return ModifyFile
     */
    public function modify(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
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
     */
    public function getSupportedActions(): array
    {
        return self::getClassConstants('MODIFY_FILE_');
    }

    /**
     * Validate this ModifyFile instance using its specific validation logic.
     * It returns true if this ModifyFile instance is well configured, i.e. if:
     * - key cannot be an empty string;
     * - action must equal to one of the class constants starting with prefix 'MODIFY_FILE_';
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        if (empty($this->filePath)) {
            $this->throwValidationException($this, "File path cannot be empty.");
        }

        // If no action is specified OR an unsupported action is given, then we throw an error.
        $modifyConstants = $this->getSupportedActions();
        $wrongActionsAndConditions = array();
        foreach ($this->actions as $action) {
            // We check if the condition object is valid
            $condition = $action['condition'];
            if (!$condition instanceof VerifyString) {
                $wrongActionsAndConditions[] = $this->getValidationException(
                    new VerifyString(''),
                    "Unsupported condition type given [%s]. Supported types are [%s]",
                    (is_object($condition) ? get_class($condition) : gettype($condition)),
                    VerifyString::class
                );
            }

            // We check if the action is supported
            if (!in_array($action['action'], $modifyConstants)) {
                $wrongActionsAndConditions[] = $this->getValidationException(
                    $this,
                    "Action %s not supported. Supported actions are [%s].",
                    $action['action'],
                    implode(', ', $modifyConstants)
                );
            }

            // We validate all the nested actions
            try {
                $condition->isValid();
            } catch (ValidationException $validationException) {
                $wrongActionsAndConditions[] = $validationException;
            }
        }

        // We check if some nested actions are not valid: if so, we throw an exception
        if ($wrongActionsAndConditions) {
            $this->throwValidationExceptionWithChildren(
                $this,
                [$wrongActionsAndConditions],
                "One or more nested actions are not valid."
            );
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
        // We check if the origin file exists
        $this->fileExists($this->filePath);

        // We open the file. we read it line by line and we modify each line if the condition is met
        $fileHandler = fopen($this->filePath, "r");
        $modifiedContent = [];
        while(! feof($fileHandler))  {
            $line = fgets($fileHandler);

            // We extract from $this->action all the condition entries,
            // which are the AbstractAction subclass instances to be run
            $runnableActions = $actionOptions = [];
            array_walk($this->actions, function (&$item, $key) use (&$runnableActions, &$actionOptions) {
                if (is_array($item)
                    && array_key_exists('condition', $item)
                        && array_key_exists('action', $item)
                            && array_key_exists('search', $item)
                                && array_key_exists('value', $item)
                                    && $item['condition'] instanceof AbstractAction
                ) {
                    $runnableActions[$key] = $item['condition'];
                    $actionOptions[$key] = [
                        'action' => $item['action'],
                        'search' => $item['search'],
                        'value' => $item['value'],
                    ];
                }
            });

            // We run the modifications configured for this action
            $this->applyWithNestedRunActions(
                $actionResult,
                $runnableActions,
                $this,
                $line,
                $actionOptions
            );

            if (!is_null($line)) {
                $modifiedContent[] = $line;
            }
        }
        fclose($fileHandler);

        // We save the new content to the original file.
        if ($this->validateResult($actionResult)) {
            // We write the modified content line by line to the same file
            $fileHandler = fopen($this->filePath, 'w+') or die("Can't open file.");
            foreach ($modifiedContent as $line) {
                fwrite($fileHandler, $line);
            }
            fclose($fileHandler);
        }

        return $actionResult;
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
        $this->fileExists($filePath);

        return file_get_contents($filePath);
    }

    /**
     * Apply the given action to the given line.
     *
     * @param string $action The action to be applied to the given line.
     * @param string $searchValue The value to be matched and modified.
     * @param string $replaceValue The value to be used by the modification action.
     * @param string $line The line to be modified.
     *
     * @return mixed|string|null
     *
     * @throws WorkerException
     */
    protected function applyActionToLine(string $action, string $searchValue, string $replaceValue, string $line)
    {
        switch ($action) {
            case self::MODIFY_FILE_APPEND_TO_LINE:
                if (StringParser::endsWith($line, PHP_EOL)) {
                    $line = trim($line, PHP_EOL) . $replaceValue . PHP_EOL;
                } else {
                    $line .= $replaceValue;
                }
                break;
            case self::MODIFY_FILE_REMOVE_IN_LINE:
                $line = str_replace($searchValue, "", $line);
                break;
            case self::MODIFY_FILE_REMOVE_LINE:
                if (StringParser::endsWith($line, PHP_EOL)) {
                    $line = PHP_EOL;
                } else {
                    $line = null;
                }
                break;
            case self::MODIFY_FILE_REPLACE_IN_LINE:
                $line = str_replace($searchValue, $replaceValue, $line);
                break;
            case self::MODIFY_FILE_REPLACE_LINE:
                $newLine = $replaceValue;
                if (StringParser::endsWith($line, PHP_EOL)) {
                    $newLine .= PHP_EOL;
                }
                $line = $newLine;
                break;
            case self::MODIFY_FILE_APPEND_TEMPLATE:
                // In this case, the replace value, should be a valid file name that we want to append
                // at the end of the current line
                $line .= $this->getFileContent($replaceValue);
                break;
            case self::MODIFY_FILE_REPLACE_WITH_TEMPLATE:
                // In this case, the replace value, should be a valid file name that we want to replace
                // to the current line
                $line = $this->getFileContent($replaceValue);
                break;
        }
        return $line;
    }

    /**
     * Run the given nested action and modify the given nested action result accordingly.
     *
     * @param AbstractAction $nestedAction The nested action to be run.
     * @param ActionResult $nestedActionResult The nested action result to be modified by
     * the given nested run action.
     * @param array $failedNestedActions A list of failed nested actions.
     * @param mixed $content The content to be used by the run method, if required.
     * @param array $actionOptions Additional options required to run the given
     * AbstractAction subclass instance.
     *
     * @throws WorkerException
     */
    public function runNestedAction(
        AbstractAction &$nestedAction,
        ActionResult &$nestedActionResult,
        array &$failedNestedActions,
        &$content = null,
        array &$actionOptions = array()
    ): void
    {
        if (array_key_exists('action', $actionOptions)
            && array_key_exists('search', $actionOptions)
                && array_key_exists('value', $actionOptions)
        ) {
            // In this case, we use the content variable as each parsed line in the original
            if ($nestedAction instanceof VerifyString) {
                $nestedActionResult = $nestedAction->checkContent(trim($content, PHP_EOL))->run();
                // If condition is matched, we modify the line
                if ($nestedAction->validateResult($nestedActionResult)) {
                    $content = $this->applyActionToLine(
                        $actionOptions['action'],
                        $actionOptions['search'],
                        $actionOptions['value'],
                        $content
                    );
                }
            }
        }
    }
}
