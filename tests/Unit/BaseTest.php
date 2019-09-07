<?php

namespace Tests\Unit;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Helpers\ThrowErrorsTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseTest.
 *
 * @package Tests\Unit
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
}