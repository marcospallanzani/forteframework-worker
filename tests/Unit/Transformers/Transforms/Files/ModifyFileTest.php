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

namespace Tests\Unit\Transformers\Transforms\Files;

use Forte\Worker\Checkers\Checks\Strings\VerifyString;
use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Exceptions\TransformException;
use Forte\Worker\Transformers\Transforms\Files\ModifyFile;
use PHPUnit\Framework\TestCase;

/**
 * Class ModifyFileTest.
 *
 * @package Tests\Unit\Transformers\Transforms\Files
 */
class ModifyFileTest extends TestCase
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
     * @return array
     */
    public function stringifyProvider(): array
    {
        // Test for action MODIFY_FILE_REPLACE_IN_LINE
        $replaceValueIfLineStartsWith = $this->getReplaceValueModifyFile();
        $this->applyContentToActions($replaceValueIfLineStartsWith);

        // Test for action MODIFY_FILE_REPLACE_LINE
        $replaceLineIfLineStartsWith = $this->getReplaceLineModifyFile();
        $this->applyContentToActions($replaceLineIfLineStartsWith);

        // Test for action MODIFY_FILE_REMOVE_IN_LINE
        $removeValueIfLineStartsWith = $this->getRemoveValueModifyFile();
        $this->applyContentToActions($removeValueIfLineStartsWith);

        // Test for action MODIFY_FILE_REMOVE_LINE
        $removeLineIfLineStartsWith = $this->getRemoveLineModifyFile();
        $this->applyContentToActions($removeLineIfLineStartsWith);

        // Test for action MODIFY_FILE_APPEND_TO_LINE
        $appendValueToLineIfLineStartsWith = $this->getAppendValueModifyFile();
        $this->applyContentToActions($appendValueToLineIfLineStartsWith);

        // Test for action MODIFY_FILE_APPEND_TEMPLATE
        $appendTemplateToLineIfLineStartsWith = $this->getAppendTemplateModifyFile();
        $this->applyContentToActions($appendTemplateToLineIfLineStartsWith);

        // Test for action MODIFY_FILE_REPLACE_WITH_TEMPLATE
        $replaceWithTemplateIfLineStartsWith = $this->getReplaceWithTemplateModifyFile();
        $this->applyContentToActions($replaceWithTemplateIfLineStartsWith);

        return [
            [$replaceValueIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Replace content 'CONTENT' with '".self::TEST_REPLACED_CONTENT."' in each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [$replaceLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Replace each line that meets the following condition with '".self::TEST_REPLACED_CONTENT."': 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [$appendValueToLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Append content '".self::TEST_APPENDED_CONTENT."' to each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [$removeValueIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Remove content 'CONTENT' in each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [$removeLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Remove each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [$appendTemplateToLineIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Append template '".self::TEST_FILE_TEMPLATE."' to each line that meets the following condition: 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [$replaceWithTemplateIfLineStartsWith, "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "0. Replace each line that meets the following condition, with template '".self::TEST_FILE_TEMPLATE."': 'Check if the given content '".self::TEST_CONTENT."' starts with the specified check value 'ANY'.';" . PHP_EOL],
            [new ModifyFile(self::TEST_FILE_MODIFY), "Apply the following transformations to the specified file '".self::TEST_FILE_MODIFY."': " . PHP_EOL . "No transformations configured." . PHP_EOL],
            [new ModifyFile(''), "Apply the following transformations to the specified file '': " . PHP_EOL . "No transformations configured." . PHP_EOL],
        ];
    }

    /**
     * Data provider for all apply tests.
     *
     * @return array
     */
    public function applyProvider(): array
    {
        return [
            [$this->getReplaceValueModifyFile(), true, false, true, 'ANY REPLACED CONTENT'],
            [$this->getReplaceLineModifyFile(),true, false, true, self::TEST_REPLACED_CONTENT],
            [$this->getRemoveValueModifyFile(), true, false, true, 'ANY '],
            [$this->getRemoveLineModifyFile(), true, false, true, ''],
            [$this->getAppendValueModifyFile(), true, false, true, self::TEST_CONTENT . self::TEST_APPENDED_CONTENT],
            [$this->getAppendTemplateModifyFile(), true, false, true, self::TEST_CONTENT . self::TEST_TEMPLATE_CONTENT],
            [$this->getReplaceWithTemplateModifyFile(), true, false, true, self::TEST_TEMPLATE_CONTENT],
            [new ModifyFile(self::TEST_FILE_MODIFY), true, false, true, self::TEST_CONTENT],
            [new ModifyFile(''), false, true, false, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedAction(), false, true, false, self::TEST_CONTENT],
            [$this->getModifyFileWithUnsupportedCondition(), false, true, false, self::TEST_CONTENT],
        ];
    }

    /**
     * Test for method ModifyFile::apply().
     *
     * @dataProvider applyProvider
     *
     * @param ModifyFile $modifyFile
     * @param bool $expected
     * @param bool $expectedException
     * @param bool $isValid
     * @param string $expectedContent
     *
     * @throws WorkerException
     */
    public function testApply(
        ModifyFile $modifyFile,
        bool $expected,
        bool $expectedException,
        bool $isValid,
        string $expectedContent
    ): void
    {
        if ($expectedException) {
            $this->expectException(WorkerException::class);
        }
        $this->assertEquals($expected, $modifyFile->run());
        $this->assertTrue($this->checkFileContent(self::TEST_FILE_MODIFY, $expectedContent));
    }

    /**
     * Test for method ModifyFile::isValid().
     *
     * @dataProvider applyProvider
     *
     * @param ModifyFile $modifyFile
     * @param bool $expected
     * @param bool $expectedException
     * @param bool $isValid
     *
     * @throws WorkerException
     */
    public function testIsValid(ModifyFile $modifyFile, bool $expected, bool $expectedException, bool $isValid): void
    {
        if ($expectedException) {
            $this->expectException(TransformException::class);
        }
        $this->assertEquals($isValid, $modifyFile->isValid());
    }

    /**
     * Test method ModifyFile::stringify().
     *
     * @dataProvider stringifyProvider
     *
     * @param ModifyFile $modifyFile
     * @param string $expectedMessage
     */
    public function testStringify(ModifyFile $modifyFile, string $expectedMessage): void
    {
        $this->assertEquals($expectedMessage, $modifyFile->stringify());
        $this->assertEquals($expectedMessage, (string) $modifyFile);
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
     * @return ModifyFile
     */
    protected function getReplaceValueModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->replaceValueIfLineStartsWith('ANY', 'CONTENT', self::TEST_REPLACED_CONTENT)
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REMOVE_IN_LINE.
     *
     * @return ModifyFile
     */
    protected function getRemoveValueModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->removeValueIfLineStartsWith('ANY', 'CONTENT')
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REMOVE_LINE.
     *
     * @return ModifyFile
     */
    protected function getRemoveLineModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->removeLineIfLineStartsWith('ANY')
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_APPEND_TO_LINE.
     *
     * @return ModifyFile
     */
    protected function getAppendValueModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->addAction(
                    ModifyFile::MODIFY_FILE_APPEND_TO_LINE,
                    VerifyString::CONDITION_STARTS_WITH,
                    'ANY',
                    self::TEST_APPENDED_CONTENT
                )
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_APPEND_TEMPLATE.
     *
     * @return ModifyFile
     */
    protected function getAppendTemplateModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->addAction(
                    ModifyFile::MODIFY_FILE_APPEND_TEMPLATE,
                    VerifyString::CONDITION_STARTS_WITH,
                    'ANY',
                    self::TEST_FILE_TEMPLATE
                )
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REPLACE_WITH_TEMPLATE.
     *
     * @return ModifyFile
     */
    protected function getReplaceWithTemplateModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->addAction(
                    ModifyFile::MODIFY_FILE_REPLACE_WITH_TEMPLATE,
                    VerifyString::CONDITION_STARTS_WITH,
                    'ANY',
                    self::TEST_FILE_TEMPLATE
                )
            ;
    }

    /**
     * Return an instance of ModifyFile to test the modification MODIFY_FILE_REPLACE_LINE.
     *
     * @return ModifyFile
     */
    protected function getReplaceLineModifyFile(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->replaceLineIfLineStartsWith('ANY', self::TEST_REPLACED_CONTENT)
            ;
    }

    /**
     * Return a bad configured ModifyFile instance.
     *
     * @return ModifyFile
     */
    protected function getModifyFileWithUnsupportedAction(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->addAction(
                    'wrong_action',
                    VerifyString::CONDITION_STARTS_WITH,
                    'ANY',
                    self::TEST_FILE_TEMPLATE
                )
            ;
    }

    /**
     * Return a bad configured ModifyFile instance.
     *
     * @return ModifyFile
     */
    protected function getModifyFileWithUnsupportedCondition(): ModifyFile
    {
        return
            (new ModifyFile(self::TEST_FILE_MODIFY))
                ->addAction(
                    ModifyFile::MODIFY_FILE_REPLACE_WITH_TEMPLATE,
                    'wrong_condition',
                    'ANY',
                    self::TEST_FILE_TEMPLATE
                )
            ;
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
            $action['condition']->setContent($content);
        });
    }
}