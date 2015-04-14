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
use QafooLabs\Refactoring\Domain\Model\LineCollection;
use QafooLabs\Refactoring\Domain\Model\EditingAction\AddMethod;
use QafooLabs\Refactoring\Domain\Model\EditingAction\ReplaceWithMethodCall;

/**
 * Extract Method Refactoring
 */
class ExtractMethod extends SingleFileRefactoring
{
    /**
     * @var LineRange
     */
    private $extractRange;

    /**
     * @var MethodSignature
     */
    private $newMethod;

    /**
     * @param string $newMethodName
     */
    public function refactor(File $file, LineRange $extractRange, $newMethodName)
    {
        $this->file = $file;
        $this->extractRange = $extractRange;

        $this->assertIsInsideMethod();

        $this->createNewMethodSignature($newMethodName);

        $this->startEditingSession();
        $this->replaceCodeWithMethodCall();
        $this->addNewMethod();
        $this->completeEditingSession();
    }

    protected function assertIsInsideMethod()
    {
        if ( ! $this->codeAnalysis->isInsideMethod($this->file, $this->extractRange)) {
            throw RefactoringException::rangeIsNotInsideMethod($this->extractRange);
        }
    }

    private function createNewMethodSignature($newMethodName)
    {
        $extractVariables = $this->variableScanner->scanForVariables($this->file, $this->extractRange);
        $methodVariables = $this->variableScanner->scanForVariables($this->file, $this->findMethodRange());

        $isStatic = $this->codeAnalysis->isMethodStatic($this->file, $this->extractRange);

        $this->newMethod = new MethodSignature(
            $newMethodName,
            $isStatic ? MethodSignature::IS_STATIC : 0,
            $methodVariables->variablesFromSelectionUsedBefore($extractVariables),
            $methodVariables->variablesFromSelectionUsedAfter($extractVariables)
        );
    }

    private function addNewMethod()
    {
        $this->session->addEdit(new AddMethod(
            $this->findMethodRange()->getEnd(),
            $this->newMethod,
            $this->getSelectedCode()
        ));
    }

    private function replaceCodeWithMethodCall()
    {
        $this->session->addEdit(new ReplaceWithMethodCall(
            $this->extractRange,
            $this->newMethod
        ));
    }

    private function findMethodRange()
    {
        return $this->codeAnalysis->findMethodRange($this->file, $this->extractRange);
    }

    private function getSelectedCode()
    {
        return LineCollection::createFromArray(
            $this->extractRange->sliceCode($this->file->getCode())
        );
    }
}
