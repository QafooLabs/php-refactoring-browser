<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
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

        $methodRange = $this->findMethodRange($file, $line);
        $definedVariables = $this->variableScanner->scanForVariables($file, $methodRange);

        if ( ! isset($definedVariables->localVariables[$oldName]) &&
             ! isset($definedVariables->assignments[$oldName])) {

            return;
        }

        $buffer = $this->editor->openBuffer($file);

        $this->replaceString($buffer, $definedVariables->localVariables, $oldName, $newName);
        $this->replaceString($buffer, $definedVariables->assignments, $oldName, $newName);

        $this->editor->save();
    }

    private function replaceString($buffer, array $variables, $oldName, $newName)
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

