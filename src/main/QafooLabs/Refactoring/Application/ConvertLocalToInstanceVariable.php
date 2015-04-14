<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\Variable;

use QafooLabs\Refactoring\Domain\Model\RefactoringException;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;
use QafooLabs\Refactoring\Domain\Model\EditingAction\AddAssignment;
use QafooLabs\Refactoring\Domain\Model\EditingAction\AddProperty;
use QafooLabs\Refactoring\Domain\Model\EditingAction\LocalVariableToInstance;

use TokenReflection\ReflectionMethod;

class ConvertLocalToInstanceVariable extends SingleFileRefactoring
{
    /**
     * @var Variable
     */
    private $convertVariable;

    /**
     * @var ReflectionMethod
     */
    private $method;

    /**
     * @param int $line
     */
    public function refactor(File $file, $line, Variable $convertVariable)
    {
        $this->file = $file;
        $this->line = $line;
        $this->convertVariable = $convertVariable;

        $this->method = $this->codeAnalysis->getMethod($this->file, LineRange::fromSingleLine($line));

        $this->assertIsInsideMethod();

        $this->startEditingSession();

        $this->addProperty();
        $this->convertVariablesToInstanceVariables();

        if ($this->variableIsMethodParameter()) {
            $this->assignArgumentVariableToInstanceVariable();
        }

        $this->completeEditingSession();
    }

    private function addProperty()
    {
        $line = $this->codeAnalysis->getLineOfLastPropertyDefinedInScope($this->file, $this->line);

        $this->session->addEdit(
            new AddProperty($line, $this->convertVariable->getName())
        );
    }

    private function convertVariablesToInstanceVariables()
    {
        $range = $this->getMethodBodyRange();

        $definedVariables = $this->getDefinedVariables();

        $this->assertVariableIsDefiniedInScope($range, $definedVariables);

        $this->session->addEdit(new LocalVariableToInstance($definedVariables, $this->convertVariable));
    }

    private function variableIsMethodParameter()
    {
        return in_array($this->convertVariable->getName(), array_map(function ($parameter) {
            return $parameter->getName();
        }, $this->method->getParameters()));
    }

    private function assignArgumentVariableToInstanceVariable()
    {
        $instanceVariable = $this->convertVariable->convertToInstance();

        $line = $this->method->getStartLine() + 1;

        // The +1 assumes that the function definition is followed by a newline with the
        // opening brace. Ideally this needs to be detected.
        $this->session->addEdit(new AddAssignment($line, $instanceVariable, $this->convertVariable->getToken()));
    }

    protected function getDefinedVariables()
    {
        $selectedMethodLineRange = $this->getMethodBodyRange();

        $definedVariables = $this->variableScanner->scanForVariables(
            $this->file, $selectedMethodLineRange
        );

        return $definedVariables;
    }

    private function getMethodBodyRange()
    {
        $methodLineRange = $this->codeAnalysis->findMethodRange($this->file, LineRange::fromSingleLine($this->line));

        return LineRange::fromLines($methodLineRange->getStart() + 1, $methodLineRange->getEnd());
    }

    private function assertVariableIsDefiniedInScope(LineRange $selectedMethodLineRange, DefinedVariables $definedVariables)
    {
        if ( ! $definedVariables->contains($this->convertVariable)) {
            throw RefactoringException::variableNotInRange($this->convertVariable, $selectedMethodLineRange);
        }
    }
}
