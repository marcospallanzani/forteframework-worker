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

use Forte\Worker\Checkers\Checks\AbstractCheck;
use Forte\Worker\Exceptions\CheckException;
use Forte\Worker\Exceptions\GeneratorException;
use Forte\Worker\Exceptions\TransformException;
use Forte\Worker\Helpers\ThrowErrorsTrait;
use Forte\Worker\Transformers\Transforms\AbstractTransform;
use PHPUnit\Framework\TestCase;

/**
 * Class ThrowErrorsTraitTest.
 *
 * @package Tests\Unit\Helpers
 */
class ThrowErrorsTraitTest extends TestCase
{
    const CHECK_TEST_MESSAGE     = "check test";
    const TRANSFORM_TEST_MESSAGE = "transform test";
    const BASE_TEST_MESSAGE      = "error message %s.";

    /**
     * Returns an anonymous AbstractCheck subclass instance to test ThrowErrorsTrait.
     *
     * @return object
     */
    protected function getAnonymousCheckClass()
    {
        return new class extends AbstractCheck {
            use ThrowErrorsTrait;
            public function isValid(): bool { return true; }
            protected function check(): bool { return true; }
            public function stringify(): string { return ThrowErrorsTraitTest::CHECK_TEST_MESSAGE; }
        };
    }

    /**
     * Returns an anonymous AbstractTransform subclass instance to test ThrowErrorsTrait.
     *
     * @return object
     */
    protected function getAnonymousTransformClass()
    {
        return new class extends AbstractTransform {
            use ThrowErrorsTrait;
            public function isValid(): bool { return true; }
            protected function apply(): bool { return true; }
            public function stringify(): string { return ThrowErrorsTraitTest::TRANSFORM_TEST_MESSAGE; }
        };
    }

    /**
     * Test ThrowErrorsTrait::throwGeneratorException() method.
     */
    public function testThrowGeneratorException(): void
    {
        $this->expectException(GeneratorException::class);
        $this->expectExceptionMessage("error message test.");
        $this->getAnonymousCheckClass()->throwGeneratorException(self::BASE_TEST_MESSAGE, "test");
    }

    /**
     * Test ThrowErrorsTrait::throwCheckException() method.
     */
    public function testThrowCheckException(): void
    {
        $this->expectException(CheckException::class);
        $this->expectExceptionMessage("error message check test.");
        $anonymousCheckClass = $this->getAnonymousCheckClass();
        $anonymousCheckClass->throwCheckException(
            $anonymousCheckClass,
            self::BASE_TEST_MESSAGE,
            self::CHECK_TEST_MESSAGE
        );
    }

    /**
     * Test ThrowErrorsTrait::throwTransformException() method.
     */
    public function testThrowTransformException(): void
    {
        $this->expectException(TransformException::class);
        $this->expectExceptionMessage("error message transform test.");
        $anonymousTransformClass = $this->getAnonymousTransformClass();
        $anonymousTransformClass->throwTransformException(
            $anonymousTransformClass,
            self::BASE_TEST_MESSAGE,
            self::TRANSFORM_TEST_MESSAGE
        );
    }
}
