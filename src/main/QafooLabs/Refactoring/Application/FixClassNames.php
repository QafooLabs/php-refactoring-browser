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
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameChange;

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
            $currentClassName = $class->declarationName();
            $phpClassName = $phpFile->extractPsr0ClassName();

            $buffer = $this->editor->openBuffer($phpFile);

            if ($phpClassName->shortName() !== $currentClassName->shortName()) {
                $line = $class->getDeclarationLine();

                $buffer->replaceString($line, $currentClassName->shortName(), $phpClassName->shortName());

                $renames[] = new PhpNameChange($currentClassName, $phpClassName);
            }

            if ($phpClassName->namespaceName() !== $currentClassName->namespaceName()) {
                $namespaceDeclarationLine = $class->getNamespaceDeclarationLine();

                $buffer->replaceString($namespaceDeclarationLine, $currentClassName->namespaceName(), $phpClassName->namespaceName());

                $renames[] = new PhpNameChange($currentClassName, $phpClassName);
            }
        }

        foreach ($names as $name) {
            foreach ($renames as $rename) {
                if ($rename->affects($name)) {
                    $buffer = $this->editor->openBuffer($name->file());
                    $buffer->replaceString($name->declaredLine(), $name->relativeName(), $rename->change($name)->relativeName());
                    continue 2;
                }
            }
        }

        $this->editor->save();
    }
}

