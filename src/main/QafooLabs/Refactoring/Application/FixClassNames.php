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

class FixClassNames
{
    private $codeAnalysis;
    private $editor;
    private $nameScanner;

    public function __construct($codeAnalysis, $editor, $nameScanner)
    {
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
        $this->nameScanner = $nameScanner;
    }

    public function refactor(Directory $directory)
    {
        $phpFiles = $directory->findAllPhpFilesRecursivly();

        $renames = array();
        $names = array();

        foreach ($phpFiles as $phpFile) {
            $classes = $this->codeAnalysis->findClasses($phpFile);
            $names = array_merge($this->nameScanner->findNames($phpFile), $names);

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

                $renames[] = array(
                    'old' => new PhpName($class->getName(), $class->getShortname(), $phpFile, $line),
                    'new' => new PhpName($phpClassName->getName(), $phpClassName->getShortname(), $phpFile, $line)
                );
            }

            $classNamespace = $class->getNamespace();

            if ($phpClassName->getNamespace() !== $classNamespace) {
                $namespaceDeclarationLine = $class->getNamespaceDeclarationLine();

                $buffer->replaceString($namespaceDeclarationLine, $classNamespace, $phpClassName->getNamespace());

                $renames[] = array(
                    'old' => new PhpName($classNamespace, $classNamespace, $phpFile, $namespaceDeclarationLine),
                    'new' => new PhpName($phpClassName->getNamespace(), $phpClassName->getNamespace(), $phpFile, $namespaceDeclarationLine)
                );
            }
        }

        foreach ($names as $name) {
            foreach ($renames as $rename) {
                if ($name->isAffectedByChangesTo($rename['old'])) {
                    $buffer = $this->editor->openBuffer($name->file());
                    $buffer->replaceString($name->declaredLine(), $name->relativeName(), $name->change($rename['old'], $rename['new'])->relativeName());
                    continue 2;
                }
            }
        }

        $this->editor->save();
    }
}

