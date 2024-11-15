<?php

namespace Forte\Worker\Tests\Unit\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class SwitchStatementTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Conditionals
 */
class SwitchStatementTest extends BaseTest
{
    /**
     * Test constants
     */
    const CASE_EXPRESSION_VALUE_1 = "value_1";
    const CASE_EXPRESSION_VALUE_2 = "value_2";
    const CASE_EXPRESSION_VALUE_3 = "value_3";
    const ADDED_VALUE_KEY_1 = "test_1";
    const ADDED_VALUE_KEY_2 = "test_2";
    const ADDED_VALUE_KEY_3 = "test_3";

    /**
     * Data provider for all switch tests.
     *
     * @return array
     */
    public function casesProvider(): array
    {
        $array = [
            'test_5' => 'value_5'
        ];

        $modifyArray1 = WorkerActionFactory::createModifyArray()->addKey(self::ADDED_VALUE_KEY_1, self::CASE_EXPRESSION_VALUE_1)->modifyContent($array);
        $modifyArray2 = WorkerActionFactory::createModifyArray()->addKey(self::ADDED_VALUE_KEY_2, self::CASE_EXPRESSION_VALUE_2)->modifyContent($array);
        $modifyArray3 = WorkerActionFactory::createModifyArray()->addKey(self::ADDED_VALUE_KEY_3, self::CASE_EXPRESSION_VALUE_3)->modifyContent($array);

        // expression | cases | default case | is valid | severity | expected result | exception expected | message
        return [
            [
                self::CASE_EXPRESSION_VALUE_1,
                [
                    [self::CASE_EXPRESSION_VALUE_1, $modifyArray1],
                    [self::CASE_EXPRESSION_VALUE_2, $modifyArray2],
                ],
                $modifyArray3,
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                [
                    self::ADDED_VALUE_KEY_1 => self::CASE_EXPRESSION_VALUE_1,
                    'test_5' => 'value_5',
                ],
                false,
                "Run the following sequence of switch case statements: " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_1."] THEN [Add value '".self::CASE_EXPRESSION_VALUE_1."' with key '".self::ADDED_VALUE_KEY_1."']; " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_2."] THEN [Add value '".self::CASE_EXPRESSION_VALUE_2."' with key '".self::ADDED_VALUE_KEY_2."']; " . PHP_EOL .
                "DEFAULT STATEMENT [Add value '".self::CASE_EXPRESSION_VALUE_3."' with key '".self::ADDED_VALUE_KEY_3."']; " . PHP_EOL
            ],
            [
                self::CASE_EXPRESSION_VALUE_2,
                [
                    [self::CASE_EXPRESSION_VALUE_1, $modifyArray1],
                    [self::CASE_EXPRESSION_VALUE_2, $modifyArray2],
                ],
                $modifyArray3,
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                [
                    self::ADDED_VALUE_KEY_2 => self::CASE_EXPRESSION_VALUE_2,
                    'test_5' => 'value_5',
                ],
                false,
                "Run the following sequence of switch case statements: " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_1."] THEN [Add value '".self::CASE_EXPRESSION_VALUE_1."' with key '".self::ADDED_VALUE_KEY_1."']; " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_2."] THEN [Add value '".self::CASE_EXPRESSION_VALUE_2."' with key '".self::ADDED_VALUE_KEY_2."']; " . PHP_EOL .
                "DEFAULT STATEMENT [Add value '".self::CASE_EXPRESSION_VALUE_3."' with key '".self::ADDED_VALUE_KEY_3."']; " . PHP_EOL
            ],
            [
                self::CASE_EXPRESSION_VALUE_3,
                [
                    [self::CASE_EXPRESSION_VALUE_1, $modifyArray1],
                    [self::CASE_EXPRESSION_VALUE_2, $modifyArray2],
                ],
                $modifyArray3,
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                [
                    self::ADDED_VALUE_KEY_3 => self::CASE_EXPRESSION_VALUE_3,
                    'test_5' => 'value_5',
                ],
                false,
                "Run the following sequence of switch case statements: " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_1."] THEN [Add value '".self::CASE_EXPRESSION_VALUE_1."' with key '".self::ADDED_VALUE_KEY_1."']; " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_2."] THEN [Add value '".self::CASE_EXPRESSION_VALUE_2."' with key '".self::ADDED_VALUE_KEY_2."']; " . PHP_EOL .
                "DEFAULT STATEMENT [Add value '".self::CASE_EXPRESSION_VALUE_3."' with key '".self::ADDED_VALUE_KEY_3."']; " . PHP_EOL
            ],
            /** Negative cases */
            /** not successful, not fatal */
            [
                self::CASE_EXPRESSION_VALUE_1,
                [
                    [self::CASE_EXPRESSION_VALUE_1, WorkerActionFactory::createFileExists()],
                ],
                $modifyArray3,
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                false,
                false,
                "Run the following sequence of switch case statements: " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_1."] THEN [Check if file '' exists.]; " . PHP_EOL .
                "DEFAULT STATEMENT [Add value '".self::CASE_EXPRESSION_VALUE_3."' with key '".self::ADDED_VALUE_KEY_3."']; " . PHP_EOL
            ],
            /** not successful, fatal */
            [
                self::CASE_EXPRESSION_VALUE_1,
                [
                    [self::CASE_EXPRESSION_VALUE_1, WorkerActionFactory::createFileExists()->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)],
                ],
                $modifyArray3,
                true,
                ActionInterface::EXECUTION_SEVERITY_FATAL,
                false,
                true,
                "Run the following sequence of switch case statements: " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_1."] THEN [Check if file '' exists.]; " . PHP_EOL .
                "DEFAULT STATEMENT [Add value '".self::CASE_EXPRESSION_VALUE_3."' with key '".self::ADDED_VALUE_KEY_3."']; " . PHP_EOL
            ],
            /** success required */
//TODO MISSING TEST CASES
            /** critical */
            [
                self::CASE_EXPRESSION_VALUE_1,
                [
                    [self::CASE_EXPRESSION_VALUE_1, WorkerActionFactory::createFileExists()->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)],
                ],
                $modifyArray3,
                true,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL,
                false,
                true,
                "Run the following sequence of switch case statements: " . PHP_EOL .
                "IF EXPRESSION VALUE IS [".self::CASE_EXPRESSION_VALUE_1."] THEN [Check if file '' exists.]; " . PHP_EOL .
                "DEFAULT STATEMENT [Add value '".self::CASE_EXPRESSION_VALUE_3."' with key '".self::ADDED_VALUE_KEY_3."']; " . PHP_EOL
            ],
        ];
    }

    /**
     * Data provider for wrong-construction tests.
     *
     * @return array
     */
    public function wrongConstructionProvider(): array
    {
        // expression | default case | case | expected exception | exception message
        return [
            // expression can be a non-object or an AbstractAction subclass instance
            [new \stdClass(), WorkerActionFactory::createFileExists(__FILE__), ['test1', WorkerActionFactory::createFileExists(__FILE__)], "The expression of a switch case should be either a non-object or an AbstractAction subclass instance. Given expression is 'Class type: stdClass. Object value: []."],
            // expression is valid, but the given case is not well formed
            [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createFileExists(__FILE__), [WorkerActionFactory::createFileExists(__FILE__)], "Wrong case detected. Expected: an array with two elements, where the first element is the expression value and the second element is the case action (AbstractAction subclass instance)."],
            ["test1", WorkerActionFactory::createFileExists(__FILE__), [WorkerActionFactory::createFileExists(__FILE__)], "Wrong case detected. Expected: an array with two elements, where the first element is the expression value and the second element is the case action (AbstractAction subclass instance)."],
            ["test1", WorkerActionFactory::createFileExists(__FILE__), ['test1'], "Wrong case detected. Expected: an array with two elements, where the first element is the expression value and the second element is the case action (AbstractAction subclass instance)."],
        ];
    }

    /**
     * Test method SwitchStatement::isValid().
     *
     * @dataProvider casesProvider
     *
     * @param $expression
     * @param array $cases
     * @param AbstractAction $defaultAction
     * @param bool $isValid
     *
     * @throws ConfigurationException
     * @throws ValidationException
     */
    public function testIsValid($expression, array $cases, AbstractAction $defaultAction, bool $isValid): void
    {
        $this->isValidTest(
            $isValid,
            WorkerActionFactory::createSwitchStatement()
                ->addExpression($expression)
                ->addCases($cases)
                ->addDefaultCase($defaultAction)
        );
    }

    /**
     * Test method SwitchStatement::__construct() with wrong construction parameters.
     *
     * @dataProvider wrongConstructionProvider
     *
     * @param $expression
     * @param AbstractAction $defaultCase
     * @param array $case
     * @param string $exceptionMessage
     *
     * @throws ConfigurationException
     */
    public function testWrongConstructionParameters(
        $expression,
        AbstractAction $defaultCase,
        array $case,
        string $exceptionMessage
    ): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage($exceptionMessage);
        WorkerActionFactory::createSwitchStatement($expression, $defaultCase, [$case]);
    }

    /**
     * Test method SwitchStatement::stringify().
     *
     * @dataProvider casesProvider
     *
     * @param $expression
     * @param array $cases
     * @param AbstractAction $defaultAction
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     *
     * @throws ConfigurationException
     */
    public function testStringify(
        $expression,
        array $cases,
        AbstractAction $defaultAction,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest(
            $message,
            WorkerActionFactory::createSwitchStatement()
                ->addExpression($expression)
                ->addCases($cases)
                ->addDefaultCase($defaultAction)
        );
    }

    /**
     * Test method SwitchStatement::run().
     *
     * @dataProvider casesProvider
     *
     * @param $expression
     * @param array $cases
     * @param AbstractAction $defaultAction
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     * @throws ConfigurationException
     */
    public function testRun(
        $expression,
        array $cases,
        AbstractAction $defaultAction,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected
    ): void
    {
        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            WorkerActionFactory::createSwitchStatement()
                ->addExpression($expression)
                ->addCases($cases)
                ->addDefaultCase($defaultAction)
                ->setActionSeverity($actionSeverity),
            $expected
        );
    }
}