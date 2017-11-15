<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\EditingAction;
use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\IndentationDetector;
use QafooLabs\Refactoring\Domain\Model\IndentingLineCollection;

use QafooLabs\Refactoring\Domain\Model\LineCollection;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Utils\ToStringIterator;

class AddMethod implements EditingAction
{
    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var MethodSignature
     */
    private $newMethod;

    /**
     * @var LineCollection
     */
    private $selectedCode;

    /**
     * @var IndentingLineCollection
     */
    private $newCode;

    /**
     * @param int $lineNumber
     */
    public function __construct(
        $lineNumber,
        MethodSignature $newMethod,
        LineCollection $selectedCode
    ) {
        $this->lineNumber   = $lineNumber;
        $this->newMethod    = $newMethod;
        $this->selectedCode = $selectedCode;
    }

    public function performEdit(EditorBuffer $buffer)
    {
        $this->newCode = new IndentingLineCollection();

        $this->newCode->addIndentation();

        $this->addMethodOpening();
        $this->addMethodBody();
        $this->addReturnStatement();
        $this->addMethodClosing();

        $buffer->append($this->lineNumber, $this->getNewCodeAsStringArray());
    }

    private function addMethodOpening()
    {
        $this->newCode->appendBlankLine();

        $this->newCode->appendString($this->getNewMethodSignatureString());
        $this->newCode->appendString('{');

        $this->newCode->addIndentation();
    }

    /**
     * @return string
     */
    private function getNewMethodSignatureString()
    {
        return sprintf(
            'private %sfunction %s(%s)',
            ($this->newMethod->isStatic() ? 'static ' : ''),
            $this->newMethod->getName(),
            $this->createVariableList($this->newMethod->arguments())
        );
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

    private function addMethodBody()
    {
        $this->newCode->appendLines($this->getUnindentedSelectedCode());
    }

    private function getUnindentedSelectedCode()
    {
        $detector = new IndentationDetector($this->selectedCode);

        $lines = array_map(function ($line) use ($detector) {
            return substr($line, $detector->getMinIndentation());
        }, iterator_to_array(new ToStringIterator($this->selectedCode->getIterator())));

        return LineCollection::createFromArray($lines);
    }

    private function addReturnStatement()
    {
        $returnVars = $this->newMethod->returnVariables();

        $numVariables = count($returnVars);

        if ($numVariables === 0) {
            return;
        }

        $returnVariable = '$' . reset($returnVars);

        if ($numVariables > 1) {
            $returnVariable = 'array(' . $this->createVariableList($returnVars) . ')';
        }

        $this->newCode->appendBlankLine();
        $this->newCode->appendString('return ' . $returnVariable . ';');
    }

    private function addMethodClosing()
    {
        $this->newCode->removeIndentation();
        $this->newCode->appendString('}');
    }


    /**
     * @return string[]
     */
    private function getNewCodeAsStringArray()
    {
        $toString = new ToStringIterator($this->newCode->getIterator());

        return iterator_to_array($toString);
    }
}
