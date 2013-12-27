<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\Variable;

class LocalVariableToInstance implements EditingAction
{
    /**
     * @var DefinedVariables
     */
    private $definedVars;

    /**
     * @var Variable
     */
    private $variable;

    public function __construct(DefinedVariables $definedVars, Variable $variable)
    {
        $this->definedVars = $definedVars;
        $this->variable    = $variable;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        $renamer = new RenameVariable(
            $this->definedVars,
            $this->variable,
            $this->variable->convertToInstance()
        );

        $renamer->performEdit($buffer);
    }
}
