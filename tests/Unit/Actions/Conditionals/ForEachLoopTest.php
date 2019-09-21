<?php

namespace Forte\Worker\Tests\Unit\Actions\Conditionals;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Conditionals\ForEachLoop;
use Forte\Worker\Actions\Factories\ActionFactory;
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
        // actions | is valid | is fatal | is success required | expected result | exception expected | message
        return [
            [
                [ActionFactory::createFileExists(__FILE__), ActionFactory::createFileExists(__FILE__)],
                true,
                false,
                false,
                true,
                false,
                "Run the following sequence of actions: \nCheck if file '".__FILE__."' exists.\nCheck if file '".__FILE__."' exists.\n"
            ],
            /** Negative cases */
            /** not successful, not fatal */
            [
                [ActionFactory::createFileExists('xxx'), ActionFactory::createFileExists('xxx')],
                true,
                false,
                false,
                false,
                false,
                "Run the following sequence of actions: \nCheck if file 'xxx' exists.\nCheck if file 'xxx' exists.\n"
            ],
            /** not successful, fatal */
            [
                [ActionFactory::createFileExists(__FILE__), ActionFactory::createMakeDirectory(__DIR__)->setIsFatal(true)],
                true,
                true,
                false,
                false,
                true,
                "Run the following sequence of actions: \nCheck if file '".__FILE__."' exists.\nCreate directory '".__DIR__."'.\n"
            ],
            /** not successful, fatal */
            [
                [ActionFactory::createFileExists(__FILE__), ActionFactory::createMakeDirectory(__DIR__)],
                true,
                false,
                true,
                false,
                true,
                "Run the following sequence of actions: \nCheck if file '".__FILE__."' exists.\nCreate directory '".__DIR__."'.\n"
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
     */
    public function testIsValid(array $actions, bool $isValid): void
    {
        $forEachLoopAction = ActionFactory::createForEachLoop($actions);
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
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     * @param string $message
     */
    public function testStringify(
        array $actions,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected,
        string $message
    ): void
    {
        $this->stringifyTest($message, ActionFactory::createForEachLoop($actions));
    }

    /**
     * Test method ForEachLoop::run().
     *
     * @dataProvider actionsProvider
     *
     * @param array $actions
     * @param bool $isValid
     * @param bool $isFatal
     * @param bool $isSuccessRequired
     * @param $expected
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testRun(
        array $actions,
        bool $isValid,
        bool $isFatal,
        bool $isSuccessRequired,
        $expected,
        bool $exceptionExpected
    ): void
    {
        $forEachLoopAction = ActionFactory::createForEachLoop();
        foreach ($actions as $action) {
            $forEachLoopAction->addAction($action);
        }

        // Basic checks
        $this->runBasicTest(
            $exceptionExpected,
            $isValid,
            $forEachLoopAction
                ->setIsFatal($isFatal)
                ->setIsSuccessRequired($isSuccessRequired),
            $expected
        );
    }
}