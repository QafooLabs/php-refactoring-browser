<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\RefactoringException;

/**
 * Rename Property Refactoring
 */
class RenameProperty extends SingleFileRefactoring
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

        $this->assertIsClassScope();

        $this->startEditingSession();
        $this->renameProperty();
        $this->completeEditingSession();
    }

    private function renameProperty()
    {
        $definedProperties = $this->getDefinedProperties();

        if ( ! $definedProperties->contains($this->oldName)) {
            throw RefactoringException::propertyNotInRange($this->oldName, LineRange::fromSingleLine($this->line));
        }

        $this->session->addEdit(new EditingAction\RenameProperty($definedProperties, $this->oldName, $this->newName));
    }
}
