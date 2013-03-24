<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\Variable;

use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

class ConvertLocalToInstanceVariable
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

    public function refactor(File $file, $line, Variable $convertVariable)
    {
        $instanceVariable = $convertVariable->convertToInstance();
        $lastPropertyLine = $this->codeAnalysis->getLineOfLastPropertyDefinedInScope($file, $line);

        $selectedMethodLineRange = $this->codeAnalysis->findMethodRange($file, $line);
        $definedVariables = $this->variableScanner->scanForVariables(
            $file, $selectedMethodLineRange
        );

        if ( ! $definedVariables->contains($convertVariable)) {
            throw RefactoringException::variableNotInRange($convertVariable, $selectedMethodLineRange);
        }

        $buffer = $this->editor->openBuffer($file);

        $session = new EditingSession($buffer);
        $session->addProperty($lastPropertyLine, $convertVariable->getName());
        $session->replaceString($definedVariables, $convertVariable, $instanceVariable);

        $this->editor->save();
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
