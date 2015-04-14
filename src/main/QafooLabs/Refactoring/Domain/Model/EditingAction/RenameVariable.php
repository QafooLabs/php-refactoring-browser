<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\Variable;

class RenameVariable implements EditingAction
{
    /**
     * @var DefinedVariables
     */
    private $definedVars;

    /**
     * @var Variable
     */
    private $oldName;

    /**
     * @var Variable
     */
    private $newName;

    public function __construct(DefinedVariables $definedVars, Variable $oldName, Variable $newName)
    {
        $this->definedVars = $definedVars;
        $this->oldName    = $oldName;
        $this->newName    = $newName;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        foreach ($this->getLinesVariableIsUsedOn() as $line) {
            $buffer->replaceString(
                $line,
                $this->oldName->getToken(),
                $this->newName->getToken()
            );
        }
    }

    /**
     * @return int[]
     */
    private function getLinesVariableIsUsedOn()
    {
        $variables = $this->definedVars->all();
        $variableName = $this->oldName->getName();

        $lines = array();

        if (isset($variables[$variableName])) {
            $lines = $variables[$variableName];
        }

        return $lines;
    }
}

