<?php

namespace QafooLabs\Refactoring\Application\Service;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Services\VariableScanner;

class ExtractMethod
{
    private $variableScanner;

    public function __construct(VariableScanner $variableScanner)
    {
        $this->variableScanner = $variableScanner;
    }

    public function refactor(File $file, LineRange $range, $newMethodName)
    {
        $patchBuilder = new \QafooLabs\Patches\PatchBuilder($file->getCode());

        $isStatic = $this->isMethodStatic($file->getCode(), $range->getEnd(), $file);

        list ($localVariables, $assignments) = $this->variableScanner->scanForVariables($file, $range);

        $methodCall = $this->generateMethodCall($newMethodName, $localVariables, $assignments, $isStatic);

        $patchBuilder->replaceLines($range->getStart(), $range->getEnd(), array($methodCall));

        $selectedCode = $range->sliceCode($file->getCode());

        $methodCode = $this->appendNewMethod($newMethodName, $selectedCode , $localVariables, $assignments, $isStatic);

        $methodEndLine = $this->getMethodEndLine($file->getCode(), $range->getEnd(), $file);
        $patchBuilder->appendToLine($methodEndLine, array_merge(array(''), $methodCode));

        return $patchBuilder->generateUnifiedDiff();
    }

    private function generateMethodCall($newMethodName, $localVariables, $assignments, $isStatic)
    {
        $ws = str_repeat(' ', 8);
        $argumentLine = $this->implodeVariables($localVariables);

        $code = $isStatic ? 'self::%s(%s);' : '$this->%s(%s);';
        $call = sprintf($code, $newMethodName, $argumentLine);

        if (count($assignments) == 1) {
            $call = '$' . $assignments[0] . ' = ' . $call;
        } else if (count($assignments) > 1) {
            $call = 'list(' . $this->implodeVariables($assignments) . ') = ' . $call;
        }

        return $ws . $call;
    }

    private function getMethodEndLine($code, $lastLine, $file)
    {
        $broker = new \TokenReflection\Broker(new \TokenReflection\Broker\Backend\Memory);
        $file = $broker->processString($code, $file->getRelativePath(), true);
        $endLineClass = 0;

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->geTclasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        return $method->getEndLine();
                    }
                }

                $endLineClass = $class->getEndLine() - 1;
            }
        }

        return $endLineClass;
    }

    private function isMethodStatic($code, $lastLine, $file)
    {
        $broker = new \TokenReflection\Broker(new \TokenReflection\Broker\Backend\Memory);
        $file = $broker->processString($code, $file->getRelativePath(), true);

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->geTclasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        return $method->isStatic();
                    }
                }
            }
        }

        return false;
    }

    private function implodeVariables($variableNames)
    {
        return implode(', ', array_map(function ($variableName) {
            return '$' . $variableName;
        }, $variableNames));
    }

    private function appendNewMethod($newMethodName, $selectedCode, $localVariables, $assignments, $isStatic)
    {
        $ws = str_repeat(' ', 8);
        $wsm = str_repeat(' ', 4);

        if (count($assignments) == 1) {
            $selectedCode[] = '';
            $selectedCode[] = $ws . 'return $' . $assignments[0] . ';';
        } else if (count($assignments) > 1) {
            $selectedCode[] = '';
            $selectedCode[] = $ws . 'return array(' . $this->implodeVariables($assignments) . ');';
        }

        $paramLine = $this->implodeVariables($localVariables);

        $methodCode = array_merge(
            array($wsm . sprintf('private%sfunction %s(%s)', $isStatic ? ' static ' : ' ', $newMethodName, $paramLine), $wsm . '{'),
            $selectedCode,
            array($wsm . '}')
        );

        return $methodCode;
    }
}
