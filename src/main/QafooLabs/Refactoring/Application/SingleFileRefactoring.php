<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;
use QafooLabs\Refactoring\Domain\Model\EditingSession;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;

abstract class SingleFileRefactoring
{
    /**
     * @var VariableScanner
     */
    protected $variableScanner;

    /**
     * @var CodeAnalysis
     */
    protected $codeAnalysis;

    /**
     * @var Editor
     */
    protected $editor;

    /**
     * @var EditingSession
     */
    protected $session;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var int
     */
    protected $line;

    public function __construct(
        VariableScanner $variableScanner,
        CodeAnalysis $codeAnalysis,
        Editor $editor
    ) {
        $this->variableScanner = $variableScanner;
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    protected function assertIsInsideMethod()
    {
        if ( ! $this->codeAnalysis->isInsideMethod($this->file, LineRange::fromSingleLine($this->line))) {
            throw RefactoringException::rangeIsNotInsideMethod(LineRange::fromSingleLine($this->line));
        }
    }

    protected function assertIsLocalScope()
    {
        if ( ! $this->codeAnalysis->isLocalScope($this->file, LineRange::fromSingleLine($this->line)) ) {
            throw RefactoringException::rangeIsNotLocalScope(LineRange::fromSingleLine($this->line));
        }
    }

    protected function startEditingSession()
    {
        $buffer = $this->editor->openBuffer($this->file);

        $this->session = new EditingSession($buffer);
    }

    protected function completeEditingSession()
    {
        $this->session->performEdits();

        $this->editor->save();
    }

    protected function getDefinedVariables()
    {
        if ($this->codeAnalysis->isInsideFunction($this->file, LineRange::fromSingleLine($this->line))) {
            $selectedMethodLineRange = $this->codeAnalysis->findFunctionRange($this->file, LineRange::fromSingleLine($this->line));
        } else {
            $selectedMethodLineRange = $this->codeAnalysis->findMethodRange($this->file, LineRange::fromSingleLine($this->line));
        }

        $definedVariables = $this->variableScanner->scanForVariables(
            $this->file, $selectedMethodLineRange
        );

        return $definedVariables;
    }
}
