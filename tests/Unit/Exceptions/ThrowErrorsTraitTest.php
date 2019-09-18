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

namespace Forte\Worker\Tests\Unit\Exceptions;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Exceptions\ConfigurationException;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ThrowErrorsTraitTest.
 *
 * @package Forte\Worker\Tests\Unit\Exceptions
 */
class ThrowErrorsTraitTest extends BaseTest
{
    /**
     * Test ThrowErrorsTrait::throwWorkerException() method.
     *
     * @throws WorkerException
     */
    public function testThrowWorkerException(): void
    {
        $this->expectException(WorkerException::class);
        $this->expectExceptionMessage("error message test.");
        $this->getAnonymousActionClass()->throwWorkerException(self::BASE_TEST_MESSAGE, "test");
    }

    /**
     * Test ThrowErrorsTrait::getWorkerException() method.
     */
    public function testGetWorkerException(): void
    {
        $anonymousActionClass = $this->getAnonymousActionClass();
        $workerException = $anonymousActionClass->getWorkerException(
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
        $this->assertInstanceOf(WorkerException::class, $workerException);
        $this->assertEquals('error message action test.', $workerException->getMessage());
    }

    /**
     * Test ThrowErrorsTrait::throwActionException() method.
     *
     * @throws ActionException
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
     *
     * @throws ValidationException
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
     * Test ThrowErrorsTrait::throwConfigurationException() method.
     *
     * @throws ConfigurationException
     */
    public function testThrowConfigurationException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("error message action test.");
        $anonymousActionClass = $this->getAnonymousActionClass();
        $anonymousActionClass->throwConfigurationException(
            $anonymousActionClass,
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
    }

    /**
     * Test ThrowErrorsTrait::getConfigurationException() method.
     */
    public function testGetConfigurationException(): void
    {
        $anonymousActionClass = $this->getAnonymousActionClass();
        $configurationException = $anonymousActionClass->getConfigurationException(
            $anonymousActionClass,
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
        $this->assertInstanceOf(ConfigurationException::class, $configurationException);
        $this->assertEquals('error message action test.', $configurationException->getMessage());
    }

    /**
     * Test ThrowErrorsTrait::throwActionExceptionWithChildren() method.
     *
     * @throws ActionException
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

    /**
     * Test ThrowErrorsTrait::throwValidationExceptionWithChildren() method.
     *
     * @throws ValidationException
     */
    public function testThrowValidationExceptionWithChildren(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("error message action test.");
        $anonymousActionClass = $this->getAnonymousActionClass();
        $anonymousActionClass->throwValidationExceptionWithChildren(
            $anonymousActionClass,
            [new ValidationException($anonymousActionClass, 'children error message action test.')],
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
    }

    /**
     * Test ThrowErrorsTrait::getValidationExceptionWithChildren() method.
     */
    public function testGetValidationExceptionWithChildren(): void
    {
        $anonymousActionClass = $this->getAnonymousActionClass();
        /** @var ValidationException $validationException */
        $validationException = $anonymousActionClass->getValidationExceptionWithChildren(
            $anonymousActionClass,
            [new ValidationException($anonymousActionClass, 'children error message action test.')],
            self::BASE_TEST_MESSAGE,
            self::ACTION_TEST_MESSAGE
        );
        $this->assertInstanceOf(ValidationException::class, $validationException);
        $this->assertCount(1, $validationException->getChildrenFailures());
        $childFailure = current($validationException->getChildrenFailures());
        $this->assertInstanceOf(ValidationException::class, $childFailure);
        $this->assertEquals('children error message action test.', $childFailure->getMessage());
    }
}
