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

use QafooLabs\Refactoring\Domain\Model\Directory;
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
        $names = $this->phpNameScanner->findNames($file);
        $class = $classes[0];

        $lastUseStatement = null;
        $usedNames = array();
        $fqcns = array();
        foreach ($names as $name) {
            if ($name->isUse()) {
                $lastUseStatement = $name->parent();
                $usedNames[] = $name->fullyQualifiedName();
            } elseif ($name->isFullyQualified()) {
                $fqcns[] = $name;
            }
        }
        $lastUseStatementLine = null !== $lastUseStatement ? $lastUseStatement->getEndLine() : $class->getNamespaceDeclarationLine()+2;

        if (count($fqcns) > 0) {

            $buffer = $this->editor->openBuffer($file);
            foreach ($fqcns as $name) {
                $buffer->replaceString($name->declaredLine(), '\\'.$name->fullyQualifiedName(), $name->shortname());

                if (!in_array($name->fullyQualifiedName(), $usedNames)) {
                    $buffer->append($lastUseStatementLine, array(sprintf('use %s;', $name->fullyQualifiedName())));
                    $lastUseStatementLine++;
                }
            }

            $this->editor->save();
        }
    }
}
