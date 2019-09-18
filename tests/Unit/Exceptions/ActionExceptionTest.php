<?php

namespace Forte\Worker\Tests\Unit\Exceptions;

use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ActionExceptionTest.
 *
 * @package Forte\Worker\Tests\Unit\Exceptions
 */
class ActionExceptionTest extends BaseTest
{
    /**
     * Test for function ActionException::toArray().
     */
    public function testToArray(): void
    {
        $grandChildException = new ActionException($this->getAnonymousActionClass('grand child class'), 'Test grand child failure');
        $childException = new ActionException($this->getAnonymousActionClass('child class'), 'Test child failure');
        $childException->addChildFailure($grandChildException);
        $actionException = new ActionException($this->getAnonymousActionClass('parent class'), 'Test primary failure');
        $actionException->addChildFailure($childException);
        $this->assertEquals(
            [
                'action' => 'parent class',
                'error_message' => 'Test primary failure',
                'children_failures' => [
                    [
                        'action' => 'child class',
                        'error_message' => 'Test child failure',
                        'children_failures' => [
                            [
                                'action' => 'grand child class',
                                'error_message' => 'Test grand child failure',
                                'children_failures' => [],
                                'error_code' => 0
                            ]
                        ],
                        'error_code' => 0
                    ]
                ],
                'error_code' => 0
            ],
            $actionException->toArray()
        );
    }
}
