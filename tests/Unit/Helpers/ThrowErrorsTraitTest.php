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
            public function isValid(): bool { return true; }
            protected function apply(): bool { return true; }
            public function stringify(): string { return ThrowErrorsTraitTest::ACTION_TEST_MESSAGE; }
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
}
