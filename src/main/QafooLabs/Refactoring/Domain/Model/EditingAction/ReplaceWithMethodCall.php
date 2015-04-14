<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\LineCollection;
use QafooLabs\Refactoring\Domain\Model\IndentationDetector;

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
        $extractedCode = $buffer->getLines($this->range);

        $buffer->replace($this->range, array($this->getIndent($extractedCode) . $this->getMethodCall()));
    }

    /**
     * @param string[] $lines
     *
     * @return string
     */
    private function getIndent(array $lines)
    {
        $detector = new IndentationDetector(
            LineCollection::createFromArray($lines)
        );

        return str_repeat(' ', $detector->getFirstLineIndentation());
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
