<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;

class AddProperty implements EditingAction
{
    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $propertyName;

    /**
     * @param int    $line
     * @param string $propertyName
     */
    public function __construct($line, $propertyName)
    {
        $this->line         = $line;
        $this->propertyName = $propertyName;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        $buffer->append($this->line, array(
            '    private $' . $this->propertyName . ';',
            ''
        ));
    }
}
