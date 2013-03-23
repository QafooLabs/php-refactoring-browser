<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\LineRange;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

/**
 * Rename Local Variable Refactoring
 */
class RenameLocalVariable
{
    /**
     * @var \QafooLabs\Refactoring\Domain\Services\VariableScanner
     */
    private $variableScanner;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\CodeAnalysis
     */
    private $codeAnalysis;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\Editor
     */
    private $editor;

    public function __construct(VariableScanner $variableScanner, CodeAnalysis $codeAnalysis, Editor $editor)
    {
        $this->variableScanner = $variableScanner;
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    public function refactor(File $file, $line, $oldName, $newName)
    {
        $oldName = ltrim($oldName, '$');
        $newName = ltrim($newName, '$');

        $definedVariables = $this->variableScanner->scanForVariables(
            $file,
            $this->findMethodRange($file, $line)
        );

        if ( ! $this->isVariableInRange($definedVariables, $oldName)) {
            return;
        }

        $buffer = $this->editor->openBuffer($file);

        $this->replaceString($buffer, $definedVariables, $oldName, $newName);

        $this->editor->save();
    }

    private function isVariableInRange(DefinedVariables $definedVariables, $oldName)
    {
        return (
            isset($definedVariables->localVariables[$oldName]) ||
            isset($definedVariables->assignments[$oldName])
        );
    }

    private function replaceString($buffer, DefinedVariables $definedVariables, $oldName, $newName)
    {
        $this->replaceStringInArray($buffer, $definedVariables->localVariables, $oldName, $newName);
        $this->replaceStringInArray($buffer, $definedVariables->assignments, $oldName, $newName);
    }

    private function replaceStringInArray($buffer, array $variables, $oldName, $newName)
    {
        if (isset($variables[$oldName])) {
            foreach ($variables[$oldName] as $line) {
                $buffer->replaceString($line, '$' . $oldName, '$' . $newName);
            }
        }
    }

    /**
     * @param File $file
     * @param integer $line
     *
     * @return LineRange
     */
    private function findMethodRange(File $file, $line)
    {
        $range = LineRange::fromSingleLine($line);
        $methodStartLine = $this->codeAnalysis->getMethodStartLine($file, $range);
        $methodEndLine = $this->codeAnalysis->getMethodEndLine($file, $range);

        return LineRange::fromLines($methodStartLine, $methodEndLine);
    }
}

