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

namespace Forte\Worker\Tests\Unit\Actions\Transforms\Files;

use Forte\Worker\Actions\ActionInterface;
use Forte\Worker\Actions\Checks\Strings\VerifyString;
use Forte\Worker\Actions\Factories\WorkerActionFactory;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Transforms\Files\ModifyFile;
use Forte\Worker\Exceptions\ValidationException;
use Forte\Worker\Tests\Unit\BaseTest;

/**
 * Class ModifyFileTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
 */
class ModifyFileTest extends BaseTest
{
    /**
     * Temporary files constants.
     */
    const TEST_FILE_TMP         = __DIR__ . '/file-tests-template';
    const TEST_FILE_MODIFY      = __DIR__ . '/file-tests-modify';
    const TEST_FILE_TEMPLATE    = __DIR__ . '/file-tests-template-to-add';
    const TEST_WRONG_FILE       = "/path/to/non/existent/file.php";
    const TEST_CONTENT          = "ANY CONTENT";
    const TEST_TEMPLATE_CONTENT = "CONTENT ADDED FROM TEMPLATE";
    const TEST_APPENDED_CONTENT = "APPENDED CONTENT";
    const TEST_REPLACED_CONTENT = "REPLACED CONTENT";

    /**
     * This method is called before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        @file_put_contents(self::TEST_FILE_TMP, self::TEST_CONTENT);
        @file_put_contents(self::TEST_FILE_TEMPLATE, self::TEST_TEMPLATE_CONTENT);
        copy(self::TEST_FILE_TMP, self::TEST_FILE_MODIFY);
    }

    /**
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        @unlink(self::TEST_FILE_TMP);
        @unlink(self::TEST_FILE_MODIFY);
        @unlink(self::TEST_FILE_TEMPLATE);
    }

    /**
     * Data provider for stringify tests.
     *
     * @param string $testName
     * @param bool $caseSensitive
     *
     * @return array
     */
    public function stringifyProvider(string $testName, bool $caseSensitive = false): array
    {
        if ($caseSensitive) {
            $caseSensitiveMessage = "(case sensitive)";
        } else {
            $caseSensitiveMessage = "(case insensitive)";
        }

        // Test for action MODIFY_FILE_REPLACE_IN_LINE
        $replaceValueIfLineStartsWith = $this->getReplaceValueModifyFile('ANY', 'CONTENT', $caseSensitive);
        $this->applyContentToActions($replaceValueIfLineStartsWith);

        // Test for action MODIFY_FILE_REPLACE_LINE
        $replaceLineIfLineStartsWith = $this->getReplaceLineModifyFile('ANY', $caseSensitive);
        $this->applyContentToActions($replaceLineIfLineStartsWith);

        // Test for action MODIFY_FILE_REMOVE_IN_LINE
        $removeValueIfLineStartsWith = $this->getRemoveValueModifyFile('ANY', 'CONTENT', $caseSensitive);
        $this->applyContentToActions($removeValueIfLineStartsWith);

        // Test for action MODIFY_FILE_REMOVE_LINE
        $removeLineIfLineStartsWith = $this->getRemoveLineModifyFile('ANY', $caseSensitive);
        $this->applyContentToActions($removeLineIfLineStartsWith);

        // Test for action MODIFY_FILE_APPEND_TO_LINE
        $appendValueToLineIfLineStartsWith = $this->getAppendValueModifyFile('ANY', $caseSensitive);
        $this->applyContentToActions($appendValueToLineIfLineStartsWith);

        // Test for action MODIFY_FILE_APPEND_TEMPLATE
        $appendTemplateToLineIfLineStartsWith = $this->getAppendTemplateModifyFile('ANY', $caseSensitive);
        $this->applyContentToActions($appendTemplateToLineIfLineStartsWith);

        // Test for action MODIFY_FILE_REPLACE_WITH_TEMPLATE
        $replaceWithTemplateIfLineStartsWith = $this->getReplaceWithTemplateModifyFile('ANY', $caseSensitive);
        $this->applyContentToActions($replaceWithTemplateIfLineStartsWith);

        return [
            [$replaceValueIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Replace content 'CONTENT' with '".self::TEST_REPLACED_CONTENT."' in each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$replaceLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Replace each line that meets the following condition with '".self::TEST_REPLACED_CONTENT."': 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$appendValueToLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Append content '".self::TEST_APPENDED_CONTENT."' to each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$removeValueIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Remove content 'CONTENT' in each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$removeLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Remove each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$appendTemplateToLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Append template '".self::TEST_FILE_TEMPLATE."' to each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$replaceWithTemplateIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Replace each line that meets the following condition, with template '".self::TEST_FILE_TEMPLATE."': 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY' $caseSensitiveMessage.';" . PHP_EOL],
            [$this->getTestInstance(self::TEST_FILE_MODIFY), "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "No transformations configured." . PHP_EOL],
            [$this->getTestInstance(), "Apply the following transformations to the specified file '': " . PHP_EOL . "No transformations configured." . PHP_EOL],
        ];
    }

    /**
     * Data provider for case-insensitive stringify tests.
     *
     * @param string $testName
     *
     * @return array
     */
    public function stringifyCaseInsensitiveProvider(string $testName): array
    {
        return $this->stringifyProvider($testName, true);
    }

    /**
     * Data provider for all apply tests.
     *
     * @return array
     */
    public function applyProvider(): array
    {
        // Action | is valid | severity | result | exception | expected content
        return [
            [$this->getReplaceValueModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY REPLACED CONTENT'], false, 'ANY REPLACED CONTENT'],
            [$this->getReplaceLineModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_REPLACED_CONTENT], false, self::TEST_REPLACED_CONTENT],
            [$this->getRemoveValueModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY '], false, 'ANY '],
            [$this->getRemoveLineModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [], false, ''],
            [$this->getAppendValueModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT . self::TEST_APPENDED_CONTENT], false, self::TEST_CONTENT . self::TEST_APPENDED_CONTENT],
            [$this->getAppendTemplateModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT . self::TEST_TEMPLATE_CONTENT], false, self::TEST_CONTENT . self::TEST_TEMPLATE_CONTENT],
            [$this->getReplaceWithTemplateModifyFile('ANY'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_TEMPLATE_CONTENT], false, self::TEST_TEMPLATE_CONTENT],
            [$this->getTestInstance(self::TEST_FILE_MODIFY), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            /** Case sensitive tests (ACTION) */
            [$this->getReplaceValueModifyFile('ANY', 'content')->caseSensitive(true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY CONTENT'], false, 'ANY CONTENT'],
            [$this->getReplaceValueModifyFile('ANY', 'content')->caseSensitive(false), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY REPLACED CONTENT'], false, 'ANY REPLACED CONTENT'],
            [$this->getReplaceValueModifyFile('ANY', 'CONTENT')->caseSensitive(false), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY REPLACED CONTENT'], false, 'ANY REPLACED CONTENT'],
            [$this->getRemoveValueModifyFile('ANY', 'content')->caseSensitive(true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY CONTENT'], false, 'ANY CONTENT'],
            [$this->getRemoveValueModifyFile('ANY', 'content')->caseSensitive(false), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY '], false, 'ANY '],
            [$this->getRemoveValueModifyFile('ANY', 'CONTENT')->caseSensitive(false), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY '], false, 'ANY '],
            /** Case sensitive tests (CHECKS) */
            [$this->getReplaceValueModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY REPLACED CONTENT'], false, 'ANY REPLACED CONTENT'],
            // The action executed correctly but didn't change the content as the case-sensitive check condition was not met (verify string)
            [$this->getReplaceValueModifyFile('any', 'CONTENT', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            [$this->getReplaceLineModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_REPLACED_CONTENT], false, self::TEST_REPLACED_CONTENT],
            [$this->getReplaceLineModifyFile('any', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            [$this->getRemoveValueModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, ['ANY '], false, 'ANY '],
            [$this->getRemoveValueModifyFile('any', 'CONTENT', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            [$this->getRemoveLineModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [], false, ''],
            [$this->getRemoveLineModifyFile('any', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            [$this->getAppendValueModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT . self::TEST_APPENDED_CONTENT], false, self::TEST_CONTENT . self::TEST_APPENDED_CONTENT],
            [$this->getAppendValueModifyFile('any', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            [$this->getAppendTemplateModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT . self::TEST_TEMPLATE_CONTENT], false, self::TEST_CONTENT . self::TEST_TEMPLATE_CONTENT],
            [$this->getAppendTemplateModifyFile('any', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            [$this->getReplaceWithTemplateModifyFile('any'), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_TEMPLATE_CONTENT], false, self::TEST_TEMPLATE_CONTENT],
            [$this->getReplaceWithTemplateModifyFile('any', true), true, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, [self::TEST_CONTENT], false, self::TEST_CONTENT],
            /** Negative cases */
            /** not successful, no fatal */
            [$this->getTestInstance(), false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedAction(), false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedCondition(), false, ActionInterface::EXECUTION_SEVERITY_NON_CRITICAL, false, false, self::TEST_CONTENT],
            /** not successful, fatal */
            [$this->getTestInstance(), false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedAction(), false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedCondition(), false, ActionInterface::EXECUTION_SEVERITY_FATAL, false, true, self::TEST_CONTENT],
//TODO SUCCESS REQUIRED MISSING
            /** successful with negative result, critical */
            [$this->getTestInstance(), false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedAction(), false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedCondition(), false, ActionInterface::EXECUTION_SEVERITY_CRITICAL, false, true, self::TEST_CONTENT],
        ];
    }

    /**
     * Test for method ModifyFile::run().
     *
     * @dataProvider applyProvider
     *
     * @param ModifyFile $modifyFile
     * @param bool $isValid
     * @param int $actionSeverity
     * @param mixed $expected
     * @param bool $expectedException
     * @param string $expectedContent
     *
     * @throws ActionException
     */
    public function testRun(
        ModifyFile $modifyFile,
        bool $isValid,
        int $actionSeverity,
        $expected,
        bool $expectedException,
        string $expectedContent
    ): void
    {
        $this->runBasicTest(
            $expectedException,
            $isValid,
            $modifyFile->setActionSeverity($actionSeverity),
            $expected
        );

        // We check if the modified file content is the expected one
        $this->assertTrue($this->checkFileContent(self::TEST_FILE_MODIFY, $expectedContent));
    }

    /**
     * Test for method ModifyFile::isValid().
     *
     * @dataProvider applyProvider
     *
     * @param ModifyFile $modifyFile
     * @param bool $isValid
     *
     * @throws ValidationException
     */
    public function testIsValid(ModifyFile $modifyFile, bool $isValid): void
    {
        $this->isValidTest($isValid, $modifyFile);
    }

    /**
     * Test method ModifyFile::stringify().
     *
     * @dataProvider stringifyProvider
     * @dataProvider stringifyCaseInsensitiveProvider
     *
     * @param ModifyFile $modifyFile
     * @param string $expectedMessage
     */
    public function testStringify(ModifyFile $modifyFile, string $expectedMessage): void
    {
        $this->stringifyTest($expectedMessage, $modifyFile);
    }

    /**
     * Return true if the given file has the expected content; false otherwise.
     *
     * @param string $filePath
     * @param string $expectedContent
     *
     * @return bool
     */
    protected function checkFileContent(string $filePath, string $expectedContent): bool
    {
        return (file_get_contents($filePath) === $expectedContent);
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REPLACE_IN_LINE.
     *
     * @param string $conditionValue
     * @param string $searchValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getReplaceValueModifyFile(
        string $conditionValue,
        string $searchValue = "CONTENT",
        bool $caseSensitive = false
    ): ModifyFile
    {
        return
            $this->getTestInstance()
                ->modify(self::TEST_FILE_MODIFY)
                ->replaceValueIfLineStartsWith($conditionValue, $searchValue, self::TEST_REPLACED_CONTENT, $caseSensitive)
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REMOVE_IN_LINE.
     *
     * @param string $conditionValue
     * @param string $searchValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getRemoveValueModifyFile(
        string $conditionValue,
        string $searchValue = "CONTENT",
        bool $caseSensitive = false
    ): ModifyFile
    {
        return
            $this->getTestInstance()
                ->modify(self::TEST_FILE_MODIFY)
                ->removeValueIfLineStartsWith($conditionValue, $searchValue, $caseSensitive)
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REMOVE_LINE.
     *
     * @param string $conditionValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getRemoveLineModifyFile(string $conditionValue, bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance()
                ->modify(self::TEST_FILE_MODIFY)
                ->removeLineIfLineStartsWith($conditionValue, $caseSensitive)
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_APPEND_TO_LINE.
     *
     * @param string $conditionValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getAppendValueModifyFile(string $conditionValue, bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance(self::TEST_FILE_MODIFY)
                ->addAction(
                    ModifyFile::MODIFY_FILE_APPEND_TO_LINE,
                    VerifyString::CONDITION_STARTS_WITH,
                    $conditionValue,
                    self::TEST_APPENDED_CONTENT,
                    "",
                    $caseSensitive
                )
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_APPEND_TEMPLATE.
     *
     * @param string $conditionValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getAppendTemplateModifyFile(string $conditionValue, bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance(self::TEST_FILE_MODIFY)
                ->addAction(
                    ModifyFile::MODIFY_FILE_APPEND_TEMPLATE,
                    VerifyString::CONDITION_STARTS_WITH,
                    $conditionValue,
                    self::TEST_FILE_TEMPLATE,
                    "",
                    $caseSensitive
                )
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REPLACE_WITH_TEMPLATE.
     *
     * @param string $conditionValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getReplaceWithTemplateModifyFile(string $conditionValue, bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance(self::TEST_FILE_MODIFY)
                ->addAction(
                    ModifyFile::MODIFY_FILE_REPLACE_WITH_TEMPLATE,
                    VerifyString::CONDITION_STARTS_WITH,
                    $conditionValue,
                    self::TEST_FILE_TEMPLATE,
                    "",
                    $caseSensitive
                )
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REPLACE_LINE.
     *
     * @param string $conditionValue
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getReplaceLineModifyFile(string $conditionValue, bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance()
                ->modify(self::TEST_FILE_MODIFY)
                ->replaceLineIfLineStartsWith($conditionValue, self::TEST_REPLACED_CONTENT, $caseSensitive)
            ;
    }

    /**
     * Return a bad configured ModifyFile instance.
     *
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getModifyFileWithUnsupportedAction(bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance(self::TEST_FILE_MODIFY)
                ->addAction(
                    'wrong_action',
                    VerifyString::CONDITION_STARTS_WITH,
                    'ANY',
                    self::TEST_FILE_TEMPLATE,
                    "",
                    $caseSensitive
                )
            ;
    }

    /**
     * Return a bad configured ModifyFile instance.
     *
     * @param bool $caseSensitive
     *
     * @return ModifyFile
     */
    protected function getModifyFileWithUnsupportedCondition(bool $caseSensitive = false): ModifyFile
    {
        return
            $this->getTestInstance(self::TEST_FILE_MODIFY)
                ->addAction(
                    ModifyFile::MODIFY_FILE_REPLACE_WITH_TEMPLATE,
                    'wrong_condition',
                    'ANY',
                    self::TEST_FILE_TEMPLATE,
                    "",
                    $caseSensitive
                )
            ;
    }

    /**
     * Create a ModifyFile instance to run the tests.
     *
     * @param string $filePath
     *
     * @return ModifyFile
     */
    protected function getTestInstance(string $filePath = "")
    {
        return WorkerActionFactory::createModifyFile($filePath);
    }

    /**
     * Apply the given content to the actions conditions of the given ModifyFile instance.
     *
     * @param ModifyFile $modifyFile
     * @param string $content
     */
    protected function applyContentToActions(ModifyFile $modifyFile, string $content = self::TEST_CONTENT): void
    {
        $actions = $modifyFile->getActions();
        array_walk($actions, function (&$action) use ($content) {
            $action['condition']->checkContent($content);
        });
    }
}
