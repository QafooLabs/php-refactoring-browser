<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\EditingSession;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

/**
 * Extract Method Refactoring
 */
class ExtractMethod
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

    public function refactor(File $file, LineRange $extractRange, $newMethodName)
    {
        if ( ! $this->codeAnalysis->isInsideMethod($file, $extractRange)) {
            throw RefactoringException::rangeIsNotInsideMethod($extractRange);
        }

        $isStatic = $this->codeAnalysis->isMethodStatic($file, $extractRange);
        $methodRange = $this->codeAnalysis->findMethodRange($file, $extractRange);
        $selectedCode = $extractRange->sliceCode($file->getCode());

        $extractVariables = $this->variableScanner->scanForVariables($file, $extractRange);
        $methodVariables = $this->variableScanner->scanForVariables($file, $methodRange);

        $buffer = $this->editor->openBuffer($file);

        $newMethod = new MethodSignature(
            $newMethodName,
            $isStatic ? MethodSignature::IS_STATIC : 0,
            array(),
            $methodVariables->variablesFromSelectionUsedAfter($extractVariables)
        );
        var_dump($newMethod);

        $session = new EditingSession($buffer);
        $session->replaceRangeWithMethodCall($extractRange, $newMethod, $extractVariables);
        $session->addMethod($methodRange->getEnd(), $newMethod, $selectedCode, $extractVariables);

        $this->editor->save();
    }
}
