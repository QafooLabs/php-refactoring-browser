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

class FixClassNames
{
    private $codeAnalysis;
    private $editor;

    public function __construct($codeAnalysis, $editor)
    {
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
    }

    public function refactor(Directory $directory)
    {
        $phpFiles = $directory->findAllPhpFilesRecursivly();

        $renamedNamespaces = array();
        $renamedClasses = array();
        $useStatements = array();

        foreach ($phpFiles as $phpFile) {
            $classes = $this->codeAnalysis->findClasses($phpFile);
            $useStatements = array_merge($this->codeAnalysis->findUseStatements($phpFile), $useStatements);

            if (count($classes) !== 1) {
                continue;
            }

            $class = $classes[0];
            $classShortname = $class->getShortName();
            $phpClassName = new PhpClassName($phpFile);

            $buffer = $this->editor->openBuffer($phpFile);

            if ($phpClassName->getShortname() !== $classShortname) {
                $line = $class->getDeclarationLine();

                $buffer->replaceString($line, $classShortname, $phpClassName->getShortname());

                $renamedClasses[$class->getName()] = $phpClassName->getName();
            }

            $classNamespace = $class->getNamespace();

            if ($phpClassName->getNamespace() !== $classNamespace) {
                $namespaceDeclarationLine = 2; // @Todo

                $buffer->replaceString($namespaceDeclarationLine, $classNamespace, $phpClassName->getNamespace());

                $renamedNamespaces[$classNamespace] = $phpClassName->getNamespace();
            }
        }

        foreach ($useStatements as $useStatement) {
            foreach ($renamedClasses as $originalClassName => $newClassName) {
                if ($useStatement->isForClass($originalClassName)) {
                    $buffer = $this->editor->openBuffer($useStatement->file());
                    $buffer->replaceString($useStatement->line(), $originalClassName, $newClassName);
                }
            }
        }

        $this->editor->save();
    }
}

