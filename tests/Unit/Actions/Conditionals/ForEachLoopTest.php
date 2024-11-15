<?php

namespace Forte\Worker\Tests\Unit\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Conditionals\ForEachLoop;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ForEachLoopTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Conditionals
 */
class ForEachLoopTest extends BaseTest
{
    /**
     * Data provider for all for-each-loop tests.
     *
     * @return array
     */
    public function actionsProvider(): array
    {
        // actions | is valid | severity | expected result | exception expected | message
        return [
            [
                [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createFileExists(__FILE__)],
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                true,
                false,
                "Run the following sequence of actions: " . PHP_EOL . "Check if file '".__FILE__."' exists." . PHP_EOL . "Check if file '".__FILE__."' exists." . PHP_EOL
            ],
            /** Negative cases */
            /** not successful, not fatal */
            [
                [WorkerActionFactory::createFileExists('xxx'), WorkerActionFactory::createFileExists('xxx')],
                true,
                ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL,
                true,
                false,
                "Run the following sequence of actions: " . PHP_EOL . "Check if file 'xxx' exists." . PHP_EOL . "Check if file 'xxx' exists." . PHP_EOL
            ],
            // Only from success required on we change the parent action result (the parent action result here is the foreach loop)
            [
                [WorkerActionFactory::createFileExists('xxx'), WorkerActionFactory::createFileExists('xxx')],
                true,
                ActionInterface::EXECUTION_SEVERITY_SUCCESS_REQUIRED,
                false,
                false,
                "Run the following sequence of actions: " . PHP_EOL . "Check if file 'xxx' exists." . PHP_EOL . "Check if file 'xxx' exists." . PHP_EOL
            ],
            /** not successful, fatal */
            [
                [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createMakeDirectory(__DIR__)->setActionSeverity(ActionInterface::EXECUTION_SEVERITY_FATAL)],
                true,
                ActionInterface::EXECUTION_SEVERITY_FATAL,
                false,
                true,
                "Run the following sequence of actions: " . PHP_EOL . "Check if file '".__FILE__."' exists." . PHP_EOL . "Create directory '".__DIR__."'." . PHP_EOL
            ],
            /** not successful, fatal -> critical */
            [
                [WorkerActionFactory::createFileExists(__FILE__), WorkerActionFactory::createMakeDirectory(__DIR__)],
                true,
                ActionInterface::EXECUTION_SEVERITY_CRITICAL,
                false,
                true,
                "Run the following sequence of actions: " . PHP_EOL . "Check if file '".__FILE__."' exists." . PHP_EOL . "Create directory '".__DIR__."'." . PHP_EOL
            ],
        ];
    }

    /**
     * Test method ForEachLoop::isValid().
     *
     * @dataProvider actionsProvider
     *
     * @param array $actions
     * @param bool $isValid
     *
     * @throws ValidationException
     * @throws ConfigurationException
     */
    public function testIsValid(array $actions, bool $isValid): void
    {
//TODO MISSING TESTS FOR CONFIGURATION EXCEPTION
        $forEachLoopAction = WorkerActionFactory::createForEachLoop($actions);
        $this->isValidTest($isValid, $forEachLoopAction);

        $registeredActions = $forEachLoopAction->getActions();
        foreach ($actions as $action) {
            if ($action instanceof AbstractAction) {
                $this->assertArrayHasKey($action->getUniqueExecutionId(), $registeredActions);
            }
        }
    }

    /**
     * Test method ForEachLoop::isValid() with wrong construction parameters.
     *
     * @throws ConfigurationException
     */
    public function testIsValidWithWrongConstructionParameters(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Invalid action detected. Found [Class type: stdClass. Object value: [].]. AbstractAction subclass instance expected.");
        new ForEachLoop([new \stdClass()]);
    }

    /**
     * Test method ForEachLoop::stringify().
     *
     * @dataProvider actionsProvider
     *
     * @param array $actions
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     *
     * @throws ConfigurationException
     */
    public function testStringify(
        array $actions,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, WorkerActionFactory::createForEachLoop($actions));
    }

    /**
     * Test method ForEachLoop::run().
     *
     * @dataProvider actionsProvider
     *
     * @param array $actions
     * @param bool $isValid
     * @param int $actionSeverity
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     * @throws ConfigurationException
     */
    public function testRun(
        array $actions,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $exceptionExpected
    ): void
    {
        $forEachLoopAction = WorkerActionFactory::createForEachLoop();
        foreach ($actions as $action) {
            $forEachLoopAction->addAction($action);
        }

        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            $forEachLoopAction->setActionSeverity($actionSeverity),
            $expected
        );
    }
}