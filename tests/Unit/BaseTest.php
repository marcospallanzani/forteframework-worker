<?php

namespace Forte\Worker\Tests\Unit;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ThrowErrorsTrait;
use Forte\Worker\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest.
 *
 * @package Forte\Worker\Tests\Unit
 */
abstract class BaseTest extends TestCase
{
    /**
     * Tests constants
     */
    const ACTION_TEST_MESSAGE = "action test";
    const BASE_TEST_MESSAGE   = "error message %s.";

    /**
     * Returns an anonymous AbstractAction subclass instance to test ThrowErrorsTrait.
     *
     * @param string $className The value returned by the stringify() method. If none
     * is specified, the class constant ACTION_TEST_MESSAGE will be used.
     *
     * @return AbstractAction
     */
    protected function getAnonymousActionClass(string $className = ''): AbstractAction
    {
        return new class($className) extends AbstractAction {
            use ThrowErrorsTrait;
            protected $className;
            public function __construct(string $className = '') { $this->className = ($className ? $className : BaseTest::ACTION_TEST_MESSAGE);}
            protected function validateInstance(): bool { return true; }
            protected function apply(ActionResult $actionResult): ActionResult { return $actionResult; }
            public function stringify(): string { return $this->className; }
            public function validateResult(ActionResult $actionResult): bool { return true; }
        };
    }

    /**
     * Base test for all isValid tests.
     *
     * @param bool $isValid
     * @param AbstractAction $action
     * @param string $exceptionMessage
     *
     * @throws ValidationException
     */
    protected function isValidTest(bool $isValid, AbstractAction $action, string $exceptionMessage = ""): void
    {
        if (!$isValid) {
            $this->expectException(ValidationException::class);
            if ($exceptionMessage) {
                $this->expectExceptionMessage($exceptionMessage);
            }
        }
        $this->assertEquals($isValid, $action->isValid());
    }

    /**
     * Base test for all stringify tests.
     *
     * @param string $expectedMessage
     * @param AbstractAction $action
     */
    protected function stringifyTest(string $expectedMessage, AbstractAction $action): void
    {
        $this->assertEquals($expectedMessage, $action->stringify());
        $this->assertEquals($expectedMessage, (string) $action);
    }

    /**
     * Base test for all run tests.
     *
     * @param bool $exceptionExpected
     * @param bool $isValid
     * @param AbstractAction $action
     * @param $expected
     * @param string $exceptionMessage
     *
     * @throws ActionException
     */
    protected function runBasicTest(
        bool $exceptionExpected,
        bool $isValid,
        AbstractAction $action,
        $expected,
        string $exceptionMessage = ""
    ): void
    {
        if ($exceptionExpected) {
            if ($isValid) {
                $this->expectException(ActionException::class);
            } else {
                $this->expectException(ValidationException::class);
            }
            if ($exceptionMessage) {
                $this->expectExceptionMessage($exceptionMessage);
            }
        }
        $this->assertEquals($expected, $action->run()->getResult());

    }
}