<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */


namespace QafooLabs\Refactoring\Adapters\PHPParser;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\PropertiesCollector;
use QafooLabs\Refactoring\Domain\Model\DefinedProperties;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;
use QafooLabs\Refactoring\Domain\Services\VariableScanner;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LocalVariableClassifier;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\NodeConnector;

class ParserVariableScanner implements VariableScanner
{
    public function scanForVariables(File $file, LineRange $range)
    {
        $stmts = $this->parse($file);

        $collector = new LineRangeStatementCollector($range);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeConnector);
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if ( ! $selectedStatements) {
            throw new \RuntimeException('No statements found in line range.');
        }

        $localVariableClassifier = new LocalVariableClassifier();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($localVariableClassifier);
        $traverser->traverse($selectedStatements);

        $localVariables = $localVariableClassifier->getUsedLocalVariables();
        $assignments = $localVariableClassifier->getAssignments();

        return new DefinedVariables($localVariables, $assignments);
    }

    public function scanForProperties(File $file, LineRange $range)
    {
        $stmts = $this->parse($file);

        $collector = new LineRangeStatementCollector($range);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if ( ! $selectedStatements) {
            throw new \RuntimeException('No statements found in line range.');
        }

        $propertiesCollector = new PropertiesCollector();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($propertiesCollector);
        $traverser->traverse($selectedStatements);

        $definitions = $propertiesCollector->getDeclarations();
        $usages = $propertiesCollector->getUsages();

        return new DefinedProperties($definitions, $usages);
    }

    private function parse(File $file)
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::PREFER_PHP7);

        return $parser->parse($file->getCode());
    }
}
