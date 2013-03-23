<?php

namespace QafooLabs\Refactoring\Application\Service;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LocalVariableClassifier;
use QafooLabs\Refactoring\Domain\Model\LineRange;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Stmt;
use PHPParser_Node_Expr_FuncCall;
use PHPParser_NodeTraverser;

class ExtractMethod
{
    public function refactor($file, LineRange $range, $newMethodName)
    {
        $code = file_get_contents($file);
        $patchBuilder = new \QafooLabs\Patches\PatchBuilder($code);

        $isStatic = $this->isMethodStatic($code, $range->getEnd(), $file);

        list ($localVariables, $assignments) = $this->scanForVariables($code, $range);

        $methodCall = $this->generateMethodCall($newMethodName, $localVariables, $assignments, $isStatic);

        $patchBuilder->replaceLines($range->getStart(), $range->getEnd(), array($methodCall));

        $selectedCode = $range->sliceCode($code);

        $methodCode = $this->appendNewMethod($newMethodName, $selectedCode , $localVariables, $assignments, $isStatic);

        $methodEndLine = $this->getMethodEndLine($code, $range->getEnd(), $file);
        $patchBuilder->appendToLine($methodEndLine, array_merge(array(''), $methodCode));

        return $patchBuilder->generateUnifiedDiff();
    }

    private function scanForVariables($code, $range)
    {
        $parser = new PHPParser_Parser();
        $stmts = $parser->parse(new PHPParser_Lexer($code));

        $collector = new LineRangeStatementCollector($range);

        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new \PHPParser_NodeVisitor_NodeConnector);
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if ( ! $selectedStatements) {
            throw new \RuntimeException("No statements found in line range.");
        }

        $localVariableClassifier = new LocalVariableClassifier();
        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor($localVariableClassifier);
        $traverser->traverse($selectedStatements);

        $localVariables = $localVariableClassifier->getUsedLocalVariables();
        $assignments = $localVariableClassifier->getAssignments();

        return array(array_unique($localVariables), array_unique($assignments), $stmts);
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
        $file = $broker->processString($code, $file, true);
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
        $file = $broker->processString($code, $file, true);

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
