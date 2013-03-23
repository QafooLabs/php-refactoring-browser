<?php

namespace QafooLabs\Refactoring\Application\Service;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Services\VariableScanner;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;

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

    public function __construct(VariableScanner $variableScanner, CodeAnalysis $codeAnalysis)
    {
        $this->variableScanner = $variableScanner;
        $this->codeAnalysis = $codeAnalysis;
    }

    public function refactor(File $file, LineRange $range, $newMethodName)
    {
        $patchBuilder = new \QafooLabs\Patches\PatchBuilder($file->getCode());

        $isStatic = $this->codeAnalysis->isMethodStatic($file, $range);

        $definedVariables = $this->variableScanner->scanForVariables($file, $range);

        $methodCall = $this->generateMethodCall($newMethodName, $definedVariables, $isStatic);

        $patchBuilder->replaceLines($range->getStart(), $range->getEnd(), array($methodCall));

        $selectedCode = $range->sliceCode($file->getCode());

        $methodCode = $this->appendNewMethod($newMethodName, $selectedCode , $definedVariables, $isStatic);

        $methodEndLine = $this->codeAnalysis->getMethodEndLine($file, $range);
        $patchBuilder->appendToLine($methodEndLine, array_merge(array(''), $methodCode));

        return $patchBuilder->generateUnifiedDiff();
    }

    private function generateMethodCall($newMethodName, $definedVariables, $isStatic)
    {
        $ws = str_repeat(' ', 8);
        $argumentLine = $this->implodeVariables($definedVariables->localVariables);

        $code = $isStatic ? 'self::%s(%s);' : '$this->%s(%s);';
        $call = sprintf($code, $newMethodName, $argumentLine);

        if (count($definedVariables->assignments) == 1) {
            $call = '$' . $definedVariables->assignments[0] . ' = ' . $call;
        } else if (count($definedVariables->assignments) > 1) {
            $call = 'list(' . $this->implodeVariables($definedVariables->assignments) . ') = ' . $call;
        }

        return $ws . $call;
    }

    private function appendNewMethod($newMethodName, $selectedCode, $definedVariables, $isStatic)
    {
        $ws = str_repeat(' ', 8);
        $wsm = str_repeat(' ', 4);

        if (count($definedVariables->assignments) == 1) {
            $selectedCode[] = '';
            $selectedCode[] = $ws . 'return $' . $definedVariables->assignments[0] . ';';
        } else if (count($definedVariables->assignments) > 1) {
            $selectedCode[] = '';
            $selectedCode[] = $ws . 'return array(' . $this->implodeVariables($definedVariables->assignments) . ');';
        }

        $paramLine = $this->implodeVariables($definedVariables->localVariables);

        $methodCode = array_merge(
            array($wsm . sprintf('private%sfunction %s(%s)', $isStatic ? ' static ' : ' ', $newMethodName, $paramLine), $wsm . '{'),
            $selectedCode,
            array($wsm . '}')
        );

        return $methodCode;
    }

    private function implodeVariables($variableNames)
    {
        return implode(', ', array_map(function ($variableName) {
            return '$' . $variableName;
        }, $variableNames));
    }
}
