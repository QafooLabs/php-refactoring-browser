<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;
use QafooLabs\Refactoring\Domain\Model\EditingAction\RenameVariable;

/**
 * Rename Local Variable Refactoring
 */
class RenameLocalVariable extends SingleFileRefactoring
{
    /**
     * @var Variable
     */
    private $oldName;

    /**
     * @var Variable
     */
    private $newName;

    /**
     * @param int $line
     */
    public function refactor(File $file, $line, Variable $oldName, Variable $newName)
    {
        $this->file = $file;
        $this->line = $line;
        $this->newName = $newName;
        $this->oldName = $oldName;

        $this->assertIsLocalScope();

        $this->assertVariableIsLocal($this->oldName);
        $this->assertVariableIsLocal($this->newName);

        $this->startEditingSession();
        $this->renameLocalVariable();
        $this->completeEditingSession();
    }

    private function assertVariableIsLocal(Variable $variable)
    {
        if ( ! $variable->isLocal()) {
            throw RefactoringException::variableNotLocal($variable);
        }
    }

    private function renameLocalVariable()
    {
        $definedVariables = $this->getDefinedVariables();

        if ( ! $definedVariables->contains($this->oldName)) {
            throw RefactoringException::variableNotInRange($this->oldName, LineRange::fromSingleLine($this->line));
        }

        $this->session->addEdit(new RenameVariable($definedVariables, $this->oldName, $this->newName));
    }
}
