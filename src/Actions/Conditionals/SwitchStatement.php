<?php

namespace Forte\Worker\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\StringHelper;

/**
 * Class SwitchStatement.
 *
 * @package Forte\Worker\Actions\Conditionals
 */
class SwitchStatement extends AbstractAction
{
    /**
     * Case constants
     */
    const EXPRESSION_VALUE = "expression-value";
    const CASE_ACTION      = "case-action";

    /**
     * @var mixed
     */
    protected $expression;

    /**
     * @var array
     */
    protected $cases = [];

    /**
     * @var AbstractAction
     */
    protected $defaultAction;

    /**
     * SwitchStatement constructor.
     *
     * @param mixed $expression The expression of this SwitchStatement instance.
     * @param AbstractAction|null $defaultAction The default action of this
     * SwitchStatement instance.
     * @param array $cases The cases to add.
     *
     * @throws ConfigurationException If one or both the given parameters are not valid.
     */
    public function __construct($expression = null, AbstractAction $defaultAction = null, array $cases = [])
    {
        parent::__construct();
        $this->addExpression($expression);
        $this->addCases($cases);
        if ($defaultAction) {
            $this->addDefaultCase($defaultAction);
        }
    }

    /**
     * Add the expression of this SwitchStatement instance. Accepted expressions
     * are either a non-object or an AbstractAction subclass instance.
     *
     * @param mixed $expression The expression of this SwitchStatement instance.
     *
     * @return $this
     *
     * @throws ConfigurationException
     */
    public function addExpression($expression): self
    {
        if (!$this->isValidExpression($expression)) {
            $this->throwConfigurationException(
                $this,
                "The expression of a switch case should be either a non-object " .
                "or an AbstractAction subclass instance. Given expression is '%s'.",
                StringHelper::stringifyVariable($expression)
            );
        }

        if ($expression instanceof AbstractAction) {
            $expression = $this->getActionForBlockExecution($expression);
        }
        $this->expression = $expression;

        return $this;
    }

    /**
     * Add the given cases list to this SwitchStatement instance. Each entry of the given list
     * should have two elements, where the first one represents the switch expression (a variable)
     * and the second one is the case action (action to run if the associated expression value is
     * equal to the set class expression).
     * e.g.
     * [
     *      [$expressionValue1, new ModifyFile()],
     *      [$expressionValue2, new CopyFile()],
     *      [$expressionValue3, new MoveFile()],
     *      ...
     *      ...
     * ]
     *
     * @param array $cases The cases to add.
     *
     * @return $this
     *
     * @throws ConfigurationException The given cases list is not well formed.
     */
    public function addCases(array $cases): self
    {
        foreach ($cases as $case) {
            if (is_array($case) && count($case) === 2) {
                $action = array_pop($case);
                $expressionValue = array_pop($case);
                if ($action instanceof AbstractAction) {
                    $this->addCase($expressionValue, $action);
                    continue;
                }
            }
            $this->throwConfigurationException(
                $this,
                "Wrong case detected. Expected: an array with two elements, where the first " .
                "element is the expression value and the second element is the case action " .
                "(AbstractAction subclass instance)."
            );
        }

        return $this;
    }

    /**
     * Add a case to this SwitchStatement instance.
     *
     * @param mixed $expressionValue The expression value that should trigger
     * the given action.
     * @param AbstractAction $action The action to be executed for the given
     * expression value.
     *
     * @return $this
     *
     * @throws ConfigurationException
     */
    public function addCase($expressionValue, AbstractAction $action): self
    {
        if (!$this->isValidExpressionValue($expressionValue)) {
            $this->throwConfigurationException(
                $this,
                "It is not possible to add a case statement with an object " .
                "as an expression value. Given expression value is '%s'.",
                StringHelper::stringifyVariable($expressionValue)
            );
        }

        $this->cases[] = [
            self::EXPRESSION_VALUE => $expressionValue,
            self::CASE_ACTION      => $this->getActionForBlockExecution($action)
        ];

        return $this;
    }

    /**
     * Add the default case for this SwitchStatement instance.
     *
     * @param AbstractAction $defaultAction The default action of this SwitchStatement instance.
     *
     * @return $this
     */
    public function addDefaultCase(AbstractAction $defaultAction): self
    {
        $this->defaultAction = $this->getActionForBlockExecution($defaultAction);

        return $this;
    }

    /**
     * Return a human-readable string representation of this
     * implementing class instance.
     *
     * @return string A human-readable string representation
     * of this implementing class instance.
     */
    public function stringify(): string
    {
        $message = "Run the following sequence of switch case statements: " . PHP_EOL;
        foreach ($this->cases as $key => $case) {
            if ($this->isValidCase($case)) {
                $message .= sprintf(
                    "IF EXPRESSION VALUE IS [%s] THEN [%s]; " . PHP_EOL,
                    $case[self::EXPRESSION_VALUE],
                    $case[self::CASE_ACTION]
                );
            }
        }

        if ($this->defaultAction instanceof AbstractAction) {
            $message .= sprintf("DEFAULT STATEMENT [%s]; " . PHP_EOL, $this->defaultAction);
        }

        return $message;
    }

    /**
     * Apply the subclass action.
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
        // If the expression is an AbstractAction subclass instance,
        // we run it and use its results as the switch-case expression
        if ($this->expression instanceof AbstractAction) {
            $expressionActionResult = $this->expression->run();
            $actionResult->addActionResult($expressionActionResult);
            $currentExpression = $expressionActionResult->getResult();
        } else {
            $currentExpression = $this->expression;
        }

        // We parse the cases list and we look for the case action to run for
        // the current case expression
        $foundCase = false;
        foreach ($this->cases as $case) {
            // This should also work for arrays
            if ($currentExpression === $case[self::EXPRESSION_VALUE]) {
                // We run the case action
                /** @var ActionResult $caseActionResult */
                $caseActionResult = $case[self::CASE_ACTION]->run();
                $actionResult->addActionResult($caseActionResult);

                // We set the just-run result as the main action result
                $actionResult->setResult($caseActionResult->getResult());
                $foundCase = true;
            }
        }

        // If no case action was run, we try to run the default one
        if (!$foundCase && $this->defaultAction instanceof AbstractAction) {
            $defaultActionResult = $this->defaultAction->run();
            $actionResult->setResult($defaultActionResult->getResult());
        }

        return $actionResult;
    }

    /**
     * Validate this AbstractAction subclass instance using a validation logic
     * specific to the current instance.
     *
     * @return bool True if no validation breaches were found; false otherwise.
     *
     * @throws \Exception If validation breaches were found.
     */
    protected function validateInstance(): bool
    {
        // At least one case block or a default action should be registered
        if (count($this->cases) === 0 && is_null($this->defaultAction)) {
            $this->throwValidationException($this, "No cases and no default action specified.");
        }

        // The expression is a required field
        if (empty($this->expression)) {
            $this->throwValidationException($this, "No expression specified.");
        }

        $wrongActions = [];
        try {
            // We validate each registered AbstractAction subclass instance.
            foreach ($this->cases as $case) {
                if ($this->isValidCase($case)) {
                    // We validate the expression value first
                    if (!$this->isValidExpressionValue($case[self::EXPRESSION_VALUE])) {
                        $this->throwWorkerException(
                            "Invalid expression value detected. Found [%s]. Non-object variable expected.",
                            StringHelper::stringifyVariable($case[self::EXPRESSION_VALUE])
                        );
                    }
                    // We validate the action
                    if (!$this->isValidCaseAction($case[self::CASE_ACTION])) {
                        $this->throwWorkerException(
                            "Invalid case action detected. Found [%s]. AbstractAction subclass instance expected.",
                            StringHelper::stringifyVariable($case[self::CASE_ACTION])
                        );
                    }
                } else {
                    $this->throwWorkerException(
                        "Invalid case block detected. Found [%s]. Array with %s and %s indexes expected.",
                        StringHelper::stringifyVariable($case),
                        self::EXPRESSION_VALUE,
                        self::CASE_ACTION
                    );
                }
            }

            // We validate the default action, if set.
            if ($this->defaultAction instanceof AbstractAction) {
                $this->defaultAction->isValid();
            } else {
                $this->throwWorkerException(
                    "Invalid default action detected. Found [%s]. AbstractAction subclass instance expected.",
                    StringHelper::stringifyVariable($this->defaultAction)
                );
            }

            // We validate the expression
            if (!$this->isValidExpression($this->expression)) {
                $this->throwWorkerException(
                    "Invalid expression detected. Found [%s]. Non-object variables " .
                    "OR AbstractAction subclass instance expected.",
                    StringHelper::stringifyVariable($this->expression)
                );
            }
        } catch (WorkerException $workerException) {
            $wrongActions[] = $workerException;
        }

        // If errors were caught, we throw a new exception with all the caught ones as children failures
        if ($wrongActions) {
            $this->throwValidationExceptionWithChildren(
                $this,
                $wrongActions,
                "One or more of the registered case blocks are not valid."
            );
        }

        return true;
    }

    /**
     * Check if the given case is valid. A valid case is an array with the following values:
     * - an entry with index self::EXPRESSION_VALUE, which represents the condition value
     *   in a switch case;
     * - an entry with index self::CASE_ACTION, which represents the action to be run;
     *
     * @param mixed $case The case to be checked.
     *
     * @return bool True if the given case is valid; false otherwise.
     */
    protected function isValidCase($case): bool
    {
        if (is_array($case)
                && array_key_exists(self::EXPRESSION_VALUE, $case)
                    && array_key_exists(self::CASE_ACTION, $case)
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if the given expression value is valid. An expression value is valid if it
     * is not an object.
     *
     * @param mixed $expressionValue The expression value to be checked.
     *
     * @return bool True if the given expression value is valid; false otherwise.
     */
    protected function isValidExpressionValue($expressionValue): bool
    {
        if (is_object($expressionValue)) {
            return false;
        }
        return true;
    }

    /**
     * Check if the given expression is valid. All types of variables are accepted.
     * If the expression is an object, then it must be an AbstractAction subclass
     * instance.
     *
     * @param mixed $expression The expression to be checked.
     *
     * @return bool True if the given expression is valid; false otherwise.
     */
    protected function isValidExpression($expression): bool
    {
        if (is_object($expression) && !$expression instanceof AbstractAction) {
            return false;
        }
        return true;
    }

    /**
     * Check if the given case action is valid. A case action is valid if it is
     * an instance of an AbstractAction subclass.
     *
     * @param mixed $action The action to be checked.
     *
     * @return bool True if the given case action is valid; false otherwise.
     */
    protected function isValidCaseAction($action): bool
    {
        if ($action instanceof AbstractAction) {
            return true;
        }
        return false;
    }

    /**
     * Clone and modify the given AbstractAction subclass instance so that it can be executed
     * in a switch case block. It sets the cloned action as FATAL, so that, in case of error,
     * an exception will be thrown and caught in the AbstractAction::run() method. The idea is
     * to stop the execution of a switch-case loop, if an error occurred in the execution of
     * any of its blocks. We also set the cloned action as NON-SUCCESS-REQUIRED, as a negative
     * action result should be accepted as a possible result.
     *
     * @param AbstractAction $blockAction The action to be modified for the execution
     * of a switch case block.
     *
     * @return AbstractAction The modified action to be used in the switch-case blocks.
     */
    protected function getActionForBlockExecution(AbstractAction $blockAction): AbstractAction
    {
        $action = clone $blockAction;
        $action
            ->setIsFatal(true)
            ->setIsSuccessRequired(false)
        ;

        return $action;
    }
}

