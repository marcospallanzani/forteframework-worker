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

use Forte\Worker\Actions\Factories\ActionFactory;
use Forte\Worker\Actions\Transforms\Files\ModifyFileContent;
use Forte\Worker\Exceptions\ActionException;
use Forte\Worker\Actions\Transforms\Files\ModifyFile;

/**
 * Class ModifyFileContentTest.
 *
 * @package Forte\Worker\Tests\Unit\Actions\Transforms\Files
 */
class ModifyFileContentTest extends ModifyFileTest
{
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
        $testsData = parent::stringifyProvider($testName, $caseSensitive);

        foreach ($testsData as &$data) {
            // We modify the expected message, which is the second field
            $data[1] = str_replace(
                "Apply the following transformations to the specified file",
                "Apply the following transformations to the specified file content (READ-ONLY)",
                $data[1]
            );
        }

        return $testsData;
    }

//TODO SUCCESS REQUIRED MISSING in parent test class

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
        $filePath = $modifyFile->getFilePath();
        $initialContent = "";
        if ($filePath) {
            $initialContent = file_get_contents($modifyFile->getFilePath());
        }

        $this->runBasicTest(
            $expectedException,
            $isValid,
            $modifyFile->setActionSeverity($actionSeverity),
            $expected
        );

        // The file content shouldn't be modified
        if ($filePath) {
            $this->assertTrue($this->checkFileContent(self::TEST_FILE_MODIFY, $initialContent));
        }
    }

    /**
     * Create a ModifyFileContent instance to run the tests.
     *
     * @param string $filePath
     *
     * @return ModifyFileContent
     */
    protected function getTestInstance(string $filePath = "")
    {
        return ActionFactory::createModifyFileContent($filePath);
    }
}
