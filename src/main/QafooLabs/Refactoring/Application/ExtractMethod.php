<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\EditingSession;

use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;

/**
 * Extract Method Refactoring
 */
class ExtractMethod
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

    public function refactor(File $file, LineRange $range, $newMethodName)
    {
        $isStatic = $this->codeAnalysis->isMethodStatic($file, $range);
        $extractedMethodEndsOnLine = $this->codeAnalysis->getMethodEndLine($file, $range);
        $selectedCode = $range->sliceCode($file->getCode());

        $definedVariables = $this->variableScanner->scanForVariables($file, $range);

        $buffer = $this->editor->openBuffer($file);

        $session = new EditingSession($buffer);
        $session->replaceRangeWithMethodCall($range, $newMethodName, $definedVariables, $isStatic);
        $session->addMethod($extractedMethodEndsOnLine, $newMethodName, $selectedCode , $definedVariables, $isStatic);

        $this->editor->save();
    }
}
