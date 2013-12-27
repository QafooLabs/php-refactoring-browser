<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\LineRange;

class ReplaceWithMethodCall implements EditingAction
{
    /**
     * @var LineRange
     */
    private $range;

    /**
     * @var MethodSignature
     */
    private $newMethod;

    public function __construct(LineRange $range, MethodSignature $newMethod)
    {
        $this->range = $range;
        $this->newMethod = $newMethod;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        $buffer->replace($this->range, array($this->getIndent() . $this->getMethodCall()));
    }

    private function getIndent()
    {
        return '        ';
    }

    private function getMethodCall()
    {
        return sprintf(
            '%s%s%s(%s);',
            $this->getReturnVariables(),
            ($this->newMethod->isStatic() ? 'self::' : '$this->'),
            $this->newMethod->getName(),
            $this->createVariableList($this->newMethod->arguments())
        );
    }

    private function getReturnVariables()
    {
        $returnVars = $this->newMethod->returnVariables();

        $numVariables = count($returnVars);

        if ($numVariables === 0) {
            return;
        }

        $returnVariable = '$' . reset($returnVars);

        if ($numVariables > 1) {
            $returnVariable = 'list(' . $this->createVariableList($returnVars) . ')';
        }

        return $returnVariable . ' = ';
    }

    /**
     * @param string[] $variables
     *
     * @return string
     */
    private function createVariableList(array $variables)
    {
        return implode(', ', array_map(function ($variableName) {
            return '$' . $variableName;
        }, $variables));
    }
}
