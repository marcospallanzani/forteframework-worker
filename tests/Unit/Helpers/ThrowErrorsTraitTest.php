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

namespace Tests\Unit\Helpers;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Helpers\ThrowErrorsTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class ThrowErrorsTraitTest.
 *
 * @package Tests\Unit\Helpers
 */
class ThrowErrorsTraitTest extends TestCase
{
    const ACTION_TEST_MESSAGE = "action test";
    const BASE_TEST_MESSAGE   = "error message %s.";

    /**
     * Returns an anonymous AbstractAction subclass instance to test ThrowErrorsTrait.
     *
     * @return object
     */
    protected function getAnonymousActionClass()
    {
        return new class extends AbstractAction {
            use ThrowErrorsTrait;
            protected function validateInstance(): bool { return true; }
            protected function apply(ActionResult $actionResult): ActionResult { return $actionResult; }
            public function stringify(): string { return ThrowErrorsTraitTest::ACTION_TEST_MESSAGE; }
            public function validateResult(ActionResult $actionResult): bool { return true; }
        };
    }

    /**
     * Test ThrowErrorsTrait::throwWorkerException() method.
     */
    public function testThrowWorkerException(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage("error message test.");
        $this->getAnonymousActionClass()->throwWorkerException(self::BASE_TEST_MESSAGE, "test");
    }

    /**
     * Test ThrowErrorsTrait::throwActionException() method.
     */
    public function testThrowActionException(): void
    {
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage("error message action test.");
        $anonymousActionClass = $this->getAnonymousActionClass();
        $anonymousActionClass->throwActionException(
            $anonymousActionClass,
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
    }

    /**
     * Test ThrowErrorsTrait::getActionException() method.
     */
    public function testGetActionException(): void
    {
        $anonymousActionClass = $this->getAnonymousActionClass();
        $actionException = $anonymousActionClass->getActionException(
            $anonymousActionClass,
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
        $this->assertInstanceOf(ActionException::class, $actionException);
        $this->assertEquals('error message action test.', $actionException->getMessage());
    }

    /**
     * Test ThrowErrorsTrait::throwActionExceptionWithChildren() method.
     */
    public function testThrowActionExceptionWithChildre(): void
    {
        $this->expectException(ActionException::class);
        $this->expectExceptionMessage("error message action test.");
        $anonymousActionClass = $this->getAnonymousActionClass();
        $anonymousActionClass->throwActionExceptionWithChildren(
            $anonymousActionClass,
            [new ActionException($anonymousActionClass, 'children error message action test.')],
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
    }

    /**
     * Test ThrowErrorsTrait::getActionExceptionWithChildren() method.
     */
    public function testGetActionExceptionWithChildre(): void
    {
        $anonymousActionClass = $this->getAnonymousActionClass();
        /** @var ActionException $actionException */
        $actionException = $anonymousActionClass->getActionExceptionWithChildren(
            $anonymousActionClass,
            [new ActionException($anonymousActionClass, 'children error message action test.')],
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
        $this->assertInstanceOf(ActionException::class, $actionException);
        $this->assertCount(1, $actionException->getChildrenFailures());
        $childFailure = current($actionException->getChildrenFailures());
        $this->assertInstanceOf(ActionException::class, $childFailure);
        $this->assertEquals('children error message action test.', $childFailure->getMessage());
    }
}
