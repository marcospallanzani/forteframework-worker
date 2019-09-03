<?php

namespace Tests\Unit\Runners;

use Forte\Worker\Actions\AbstractAction;
use Forte\Worker\Actions\Checks\Files\FileExists;
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
            // dir path | number of failed actions | action instance | before action instance
            [__DIR__, 0, new EmptyTransform(), new FileExists(__FILE__)],
            [__DIR__, 0, null, null],
            [__DIR__, 1, new EmptyTransform(), new FileExists('wrong-file')],
        ];
    }

    /**
     * Test a successful call to the method ProjectRunner::applyActions().
     *
     * @dataProvider runnerProvider
     *
     * @param string $dirPath
     * @param AbstractAction $action
     * @param AbstractAction $beforeAction
     * @param int $failedActions
     */
    public function testProjectRunnerSuccess(
        string $dirPath,
        int $failedActions,
        AbstractAction $action = null,
        AbstractAction $beforeAction = null
    ): void
    {
        $projectRunner = new ProjectRunner($dirPath);
        if (!is_null($action) && !is_null($beforeAction)) {
            $projectRunner->addAction($action->addBeforeAction($beforeAction));
        }
        $this->assertCount($failedActions, $projectRunner->applyActions());
    }
}
