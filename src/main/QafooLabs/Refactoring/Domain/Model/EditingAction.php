<?php

namespace QafooLabs\Refactoring\Domain\Model;

interface EditingAction
{
    public function performEdit(EditorBuffer $buffer);
}
