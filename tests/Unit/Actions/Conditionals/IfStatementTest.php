<?php

namespace Forte\Worker\Tests\Unit\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Factories\ActionFactory;
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
        // if-statements | is valid | is fatal | is success required | expected result | exception expected | message
        return [
            [
                ActionFactory::createFileExists(__FILE__),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [ActionFactory::createFileExists(__FILE__), ActionFactory::createFileExists(__FILE__)]
                ],
                true,
                false,
                false,
                true,
                false,
                "Run the following chain of if-else statements: \nIF [Check if file '".__FILE__."' exists.] THEN [Check if file '".__FILE__."' exists.]; \nDEFAULT CONDITION [Check if file '".__FILE__."' exists.]"
            ],
            /** Negative cases */
            /** not successful, not fatal */
            [
                ActionFactory::createFileExists('xxx'),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [ActionFactory::createFileExists('xxx'), ActionFactory::createFileExists('xxx')]
                ],
                true,
                false,
                false,
                false,
                false,
                "Run the following chain of if-else statements: \nIF [Check if file 'xxx' exists.] THEN [Check if file 'xxx' exists.]; \nDEFAULT CONDITION [Check if file 'xxx' exists.]"
            ],
            /** not successful, fatal */
            [
                ActionFactory::createFileExists(__FILE__),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [ActionFactory::createFileExists(__FILE__), ActionFactory::createMakeDirectory(__DIR__)->setIsFatal(true)]
                ],
                true,
                true,
                false,
                false,
                true,
                "Run the following chain of if-else statements: \nIF [Check if file '".__FILE__."' exists.] THEN [Create directory '".__DIR__."'.]; \nDEFAULT CONDITION [Check if file '".__FILE__."' exists.]"
            ],
            /** not successful, fatal */
            [
                ActionFactory::createFileExists(__FILE__),
                // We have to wrap the array [condition, run-action] into another array,
                // as the method createFileExists is a variadic function
                [
                    [ActionFactory::createFileExists(__FILE__), ActionFactory::createMakeDirectory(__DIR__)]
                ],
                true,
                false,
                true,
                false,
                true,
                "Run the following chain of if-else statements: \nIF [Check if file '".__FILE__."' exists.] THEN [Create directory '".__DIR__."'.]; \nDEFAULT CONDITION [Check if file '".__FILE__."' exists.]"
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
        $this->isValidTest($isValid, ActionFactory::createIfStatement($defaultAction, $ifStatements));
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
        $this->isValidTest(false, ActionFactory::createIfStatement(null, [ActionFactory::createFileExists(__FILE__)]));
    }

    /**
     * Test method IfStatement::stringify().
     *
     * @dataProvider statementsProvider
     *
     * @param AbstractAction $defaultAction
     * @param array $ifStatements
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
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
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, ActionFactory::createIfStatement($defaultAction, $ifStatements));
    }

    /**
     * Test method IfStatement::run().
     *
     * @dataProvider statementsProvider
     *
     * @param AbstractAction $defaultAction
     * @param array $ifStatements
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
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
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected
    ): void
    {
        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            ActionFactory::createIfStatement($defaultAction)
                ->addStatements($ifStatements)
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired),
            $expected
        );
    }
}