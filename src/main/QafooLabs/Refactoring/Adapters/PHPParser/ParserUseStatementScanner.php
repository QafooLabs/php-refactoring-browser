<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\UseStatementCollector;
use QafooLabs\Refactoring\Domain\Services\UseStatementScanner;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpUseStatement;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_NodeTraverser;

class ParserUseStatementScanner implements UseStatementScanner
{
    public function findUseStatements(File $file)
    {
        $parser    = new PHPParser_Parser(new PHPParser_Lexer());
        $collector = new UseStatementCollector();
        $traverser = new PHPParser_NodeTraverser;

        $stmts = $parser->parse($file->getCode());

        $traverser->addVisitor($collector);
        $traverser->traverse($stmts);

        return array_map(function ($use) use ($file) {
            return new PhpUseStatement($file, $use['name'], $use['line']);
        }, $collector->collectedUseDeclarations());
    }
}
