<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\PhpNameCollector;
use QafooLabs\Refactoring\Domain\Services\PhpNameScanner;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_NodeTraverser;

class ParserPhpNameScanner implements PhpNameScanner
{
    public function findNames(File $file)
    {
        $parser    = new PHPParser_Parser(new PHPParser_Lexer());
        $collector = new PhpNameCollector();
        $traverser = new PHPParser_NodeTraverser;

        $stmts = $parser->parse($file->getCode());

        $traverser->addVisitor($collector);
        $traverser->traverse($stmts);

        return array_map(function ($use) use ($file) {
            return new PhpName($use['fqcn'], $use['alias'], $file, $use['line']);
        }, $collector->collectedNameDeclarations());
    }
}
