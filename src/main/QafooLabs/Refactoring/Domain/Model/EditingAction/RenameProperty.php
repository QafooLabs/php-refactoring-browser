<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\DefinedProperties;
use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\Variable;

class RenameProperty implements EditingAction
{
    /**
     * @var DefinedProperties
     */
    private $definedProperties;

    /**
     * @var Variable
     */
    private $oldName;

    /**
     * @var Variable
     */
    private $newName;

    public function __construct(DefinedProperties $definedProperties, Variable $oldName, Variable $newName)
    {
        $this->definedProperties = $definedProperties;
        $this->oldName = $oldName;
        $this->newName = $newName;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        if ($line = $this->getLinePropertyIsDeclaredOn()) {
            $buffer->replaceString(
                $line,
                $this->oldName->getToken(),
                $this->newName->getToken()
            );
        }

        foreach ($this->getLinesPropertyIsUsedOn() as $line) {
            $buffer->replaceString(
                $line,
                $this->oldName->convertToInstance()->getToken(),
                $this->newName->convertToInstance()->getToken()
            );
        }
    }

    /**
     * @return int[]
     */
    private function getLinesPropertyIsUsedOn()
    {
        return $this->definedProperties->usages($this->oldName->getName());
    }

    /**
     * @return int
     */
    private function getLinePropertyIsDeclaredOn()
    {
        return $this->definedProperties->declaration($this->oldName->getName());
    }
}

