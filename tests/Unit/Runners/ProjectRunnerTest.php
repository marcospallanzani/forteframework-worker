<?php

namespace Tests\Unit\Runners;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\ActionResult;
use Forte\Worker\Actions\Checks\Files\DirectoryExists;
use Forte\Worker\Actions\Checks\Files\FileExists;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Runners\ProjectRunner;
use Forte\Worker\Actions\Transforms\EmptyTransform;
use PHPUnit\Framework\TestCase;

/**
 * Class ProjectRunnersTest.
 *
 * @package Tests\Unit\Runners
 */
class ProjectRunnerTest extends TestCase
{
    /**
     * Data provider for runner tests.
     *
     * @return array
     */
    public function runnerProvider(): array
    {
        return [
            [
                __DIR__,
                0,
                2,
                [
                    (new FileExists(__FILE__))->addBeforeAction(new FileExists(__FILE__)),
                    (new FileExists(__FILE__))->addBeforeAction(new DirectoryExists(__DIR__)),
                ],
                false
            ],
            [
                __DIR__,
                0,
                0,
                [
                    null,
                    null,
                ],
                false
            ],
            [
                __DIR__,
                0,
                2,
                [
//TODO AT THE MOMENT THE FAILURE OF A BEFORE ACTION DOES NOT CHANGE THE RESULT OF THE MAIN ACTION: THIS SHOULD BE CHANGED
                    (new FileExists(__FILE__))->addBeforeAction((new FileExists('wrong-file'))),
                    (new FileExists(__FILE__))->addBeforeAction(new DirectoryExists(__DIR__)),
                ],
                false
            ],
            [
                __DIR__,
                0,
                2,
                [
                    (new FileExists(__FILE__))->addBeforeAction((new FileExists('wrong-file'))->setIsSuccessRequired(true)),
                    (new FileExists(__FILE__))->addBeforeAction(new DirectoryExists(__DIR__)),
                ],
                true
            ],
            [
                __DIR__,
                2,
                0,
                [
                    (new FileExists('wrong-file')),
                    (new DirectoryExists("wrong-directory")),
                ],
                false
            ],
            [
                __DIR__,
                2,
                0,
                [
                    (new FileExists('wrong-file'))->setIsSuccessRequired(true),
                    (new DirectoryExists("wrong-directory")),
                ],
                true
            ],
        ];
    }

    /**
     * Test method ProjectRunner::applyActions().
     *
     * @dataProvider runnerProvider
     *
     * @param string $dirPath
     * @param int $failedActions
     * @param int $successfulActions
     * @param array $actions
     * @param bool $exceptionExpected
     *
     * @throws ActionException
     */
    public function testProjectRunner(
        string $dirPath,
        int $failedActions,
        int $successfulActions,
        array $actions,
        bool $exceptionExpected
    ): void
    {
        if ($exceptionExpected) {
            $this->expectException(ActionException::class);
        }

        $projectRunner = new ProjectRunner($dirPath);

        // We assert that the project paths have been correctly set
        $this->assertEquals($dirPath, $projectRunner->getProjectFolder());
        $this->assertEquals(dirname($dirPath), $projectRunner->getInstallationFolder());

        $addedActions = [];
        foreach ($actions as $action) {
            if ($action instanceof AbstractAction) {
                $projectRunner->addAction($action);
                $addedActions[] = $action;
            }
        }

        // We assert that the action has been correctly added to this runner instance
        $this->assertEquals($addedActions, $projectRunner->getActions());

        $actionResults = $projectRunner->applyActions();
        $failed = $success = 0;
        foreach ($actionResults as $actionResult) {
            if ($actionResult instanceof ActionResult) {
                $currentAction = $actionResult->getAction();
                if ($currentAction->validateResult($actionResult)) {
                    $success++;
                } else {
                    $failed++;
                }
            }
        }

        // We assert the number of failed and successful actions
        $this->assertEquals($successfulActions, $success);
        $this->assertEquals($failedActions, $failed);
    }
}
