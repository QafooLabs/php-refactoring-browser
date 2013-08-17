<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\PhpNameCollector;
use QafooLabs\Refactoring\Domain\Services\PhpNameScanner;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\UseStatement;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameOccurance;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_NodeTraverser;
use PHPParser_Error;

class ParserPhpNameScanner implements PhpNameScanner
{
    public function findNames(File $file)
    {
        $parser    = new PHPParser_Parser(new PHPParser_Lexer());
        $collector = new PhpNameCollector();
        $traverser = new PHPParser_NodeTraverser;

        try {
            $stmts = $parser->parse($file->getCode());
        } catch (PHPParser_Error $e) {
            throw new \RuntimeException("Error parsing " . $file->getRelativePath() .": " . $e->getMessage(), 0, $e);
        }

        $traverser->addVisitor($collector);
        $traverser->traverse($stmts);

        return array_map(function ($use) use ($file) {
            $type = constant('QafooLabs\Refactoring\Domain\Model\PhpName::TYPE_' . strtoupper($use['type']));
            return new PhpNameOccurance(
                new PhpName(
                    $use['fqcn'],
                    $use['alias'],
                    $type
                ),
                $file,
                $use['line']
            );
        }, $collector->collectedNameDeclarations());
    }
}
