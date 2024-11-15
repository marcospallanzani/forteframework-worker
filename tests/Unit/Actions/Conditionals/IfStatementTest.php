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
 * Class IfStatementTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Conditionals
 */
class IfStatementTest extends BaseTest
{
    /**
     * Data provider for all statement tests.
     *
     * @return array
     */
    public function statementsProvider(): array
    {
        // default action | if-statements | is valid | severity | expected result | exception expected | message
        return [
            [
                WorkerActionFactory::createFileExists(__FILE__),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createFileExists(__FILE__)]
                ],
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                true,
                false,
                "Run the following chain of if-else statements: " . PHP_EOL . "IF [Check if file '".__FILE__."' exists.] THEN [Check if file '".__FILE__."' exists.]; " . PHP_EOL . "DEFAULT CONDITION [Check if file '".__FILE__."' exists.]"
            ],
            /** Negative cases */
            /** not successful, not fatal */
            [
                WorkerActionFactory::createFileExists('xxx'),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [WorkerActionFactory::createFileExists('xxx'), WorkerActionFactory::createFileExists('xxx')]
                ],
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                false,
                false,
                "Run the following chain of if-else statements: " . PHP_EOL . "IF [Check if file 'xxx' exists.] THEN [Check if file 'xxx' exists.]; " . PHP_EOL . "DEFAULT CONDITION [Check if file 'xxx' exists.]"
            ],
            /** not successful, fatal */
            [
                WorkerActionFactory::createFileExists(__FILE__),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createMakeDirectory(__DIR__)->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)]
                ],
                true,
                ActionInterface::EXECUTION_SEVERITY_FATAL,
                false,
                true,
                "Run the following chain of if-else statements: " . PHP_EOL . "IF [Check if file '".__FILE__."' exists.] THEN [Create directory '".__DIR__."'.]; " . PHP_EOL . "DEFAULT CONDITION [Check if file '".__FILE__."' exists.]"
            ],
//TODO MISSING SUCCESS-REQUIRED CASE
            /** not successful, fatal -> critical */
            [
                WorkerActionFactory::createFileExists(__FILE__),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createMakeDirectory(__DIR__)]
                ],
                true,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL,
                false,
                true,
                "Run the following chain of if-else statements: " . PHP_EOL . "IF [Check if file '".__FILE__."' exists.] THEN [Create directory '".__DIR__."'.]; " . PHP_EOL . "DEFAULT CONDITION [Check if file '".__FILE__."' exists.]"
            ],
        ];
    }

    /**
     * Test method IfStatement::isValid().
     *
     * @dataProvider statementsProvider
     *
     * @param AbstractAction $defaultAction
     * @param array $ifStatements
     * @param bool $isValid
     *
     * @throws ConfigurationException
     * @throws ValidationException
     */
    public function testIsValid(AbstractAction $defaultAction, array $ifStatements, bool $isValid): void
    {
        $this->isValidTest($isValid, WorkerActionFactory::createIfStatement($defaultAction, $ifStatements));
    }

    /**
     * Test method IfStatement::isValid() with wrong construction parameters.
     *
     * @throws ConfigurationException
     * @throws ValidationException
     */
    public function testIsValidWithWrongConstructionParameters(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->isValidTest(false, WorkerActionFactory::createIfStatement(null, [WorkerActionFactory::createFileExists(__FILE__)]));
    }

    /**
     * Test method IfStatement::stringify().
     *
     * @dataProvider statementsProvider
     *
     * @param AbstractAction $defaultAction
     * @param array $ifStatements
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     *
     * @throws ConfigurationException
     */
    public function testStringify(
        AbstractAction $defaultAction,
        array $ifStatements,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, WorkerActionFactory::createIfStatement($defaultAction, $ifStatements));
    }

    /**
     * Test method IfStatement::run().
     *
     * @dataProvider statementsProvider
     *
     * @param AbstractAction $defaultAction
     * @param array $ifStatements
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     * @throws ConfigurationException
     */
    public function testRun(
        AbstractAction $defaultAction,
        array $ifStatements,
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
            WorkerActionFactory::createIfStatement($defaultAction)
                ->addStatements($ifStatements)
                ->setActionSeverity($actionSeverity),
            $expected
        );
    }
}