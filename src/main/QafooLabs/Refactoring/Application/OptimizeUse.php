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

namespace QafooLabs\Refactoring\Application;


use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpClassName;
use QafooLabs\Refactoring\Domain\Model\PhpName;

class OptimizeUse
{
    private $codeAnalysis;
    private $editor;
    private $phpNameScanner;

    public function __construct($codeAnalysis, $editor, $phpNameScanner)
    {
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
        $this->phpNameScanner = $phpNameScanner;
    }

    public function refactor(File $file)
    {
        $classes = $this->codeAnalysis->findClasses($file);
        $occurances = $this->phpNameScanner->findNames($file);
        $class = $classes[0];

        $appendNewLine = 0 === $class->namespaceDeclarationLine();
        $lastUseStatementLine = $class->namespaceDeclarationLine() + 2;
        $usedNames = array();
        $fqcns = array();

        foreach ($occurances as $occurance) {
            $name = $occurance->name();

            if ($name->type() === PhpName::TYPE_NAMESPACE || $name->type() === PhpName::TYPE_CLASS) {
                continue;
            }

            if ($name->isUse()) {
                $lastUseStatementLine = $occurance->declarationLine();
                $usedNames[] = $name->fullyQualifiedName();
            } elseif ($name->isFullyQualified()) {
                $fqcns[] = $occurance;
            }
        }

        if (!$fqcns) {
            return;
        }

        $buffer = $this->editor->openBuffer($file);

        foreach ($fqcns as $occurance) {
            $name = $occurance->name();
            $buffer->replaceString($occurance->declarationLine(), '\\'.$name->fullyQualifiedName(), $name->shortname());

            if (!in_array($name->fullyQualifiedName(), $usedNames)) {
                $lines = array(sprintf('use %s;', $name->fullyQualifiedName()));
                if ($appendNewLine) {
                    $appendNewLine = FALSE;
                    $lines[] = '';
                }

                $buffer->append($lastUseStatementLine, $lines);
                $lastUseStatementLine++;
            }
        }

        $this->editor->save();
    }
}
