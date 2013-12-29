<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\Variable;

class AddAssignment implements EditingAction
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var Variable
     */
    private $lhs;

    /**
     * @var string
     */
    private $rhs;

    /**
     * @param int    $line
     * @param string $rhs
     */
    public function __construct($line, Variable $lhs, $rhs)
    {
        $this->line = $line;
        $this->lhs  = $lhs;
        $this->rhs  = $rhs;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        $buffer->append($this->line, array(
            '        ' . $this->lhs->getToken() . ' = ' . $this->rhs . ';',
            ''
        ));
    }
}
