<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;

class ConvertLocalToInstanceVariable
{
    /**
     * @var \QafooLabs\Refactoring\Domain\Services\VariableScanner
     */
    private $variableScanner;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\CodeAnalysis
     */
    private $codeAnalysis;

    /**
     * @var \QafooLabs\Refactoring\Domain\Services\Editor
     */
    private $editor;

    public function __construct(VariableScanner $variableScanner, CodeAnalysis $codeAnalysis, Editor $editor)
    {
        $this->variableScanner = $variableScanner;
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    public function refactor(File $file, $line, Variable $convertVariable)
    {
        $definedVariables = $this->variableScanner->scanForVariables(
            $file,
            $this->findMethodRange($file, $line)
        );
    }

    /**
     * @param File $file
     * @param integer $line
     *
     * @return LineRange
     */
    private function findMethodRange(File $file, $line)
    {
        $range = LineRange::fromSingleLine($line);
        $methodStartLine = $this->codeAnalysis->getMethodStartLine($file, $range);
        $methodEndLine = $this->codeAnalysis->getMethodEndLine($file, $range);

        return LineRange::fromLines($methodStartLine, $methodEndLine);
    }
}
