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

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Tests\Unit\BaseTest;

/**
 * Class ThrowErrorsTraitTest.
 *
 * @package Tests\Unit\Helpers
 */
class ThrowErrorsTraitTest extends BaseTest
{
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
     * Test ThrowErrorsTrait::throwValidationException() method.
     */
    public function testThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("error message action test.");
        $anonymousActionClass = $this->getAnonymousActionClass();
        $anonymousActionClass->throwValidationException(
            $anonymousActionClass,
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
    }

    /**
     * Test ThrowErrorsTrait::getValidationException() method.
     */
    public function testGetValidationException(): void
    {
        $anonymousActionClass = $this->getAnonymousActionClass();
        $validationException = $anonymousActionClass->getValidationException(
            $anonymousActionClass,
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
        $this->assertInstanceOf(ValidationException::class, $validationException);
        $this->assertEquals('error message action test.', $validationException->getMessage());
    }

    /**
     * Test ThrowErrorsTrait::throwActionExceptionWithChildren() method.
     */
    public function testThrowActionExceptionWithChildren(): void
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
    public function testGetActionExceptionWithChildren(): void
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
