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

use QafooLabs\Collections\Set;
use QafooLabs\Refactoring\Domain\Model\Directory;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameChange;
use QafooLabs\Refactoring\Domain\Model\PhpNames\NoImportedUsagesFilter;
use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Services\Editor;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner;
use QafooLabs\Refactoring\Domain\Model\PhpNameOccurance;

class FixClassNames
{
    /**
     * @var CodeAnalysis
     */
    private $codeAnalysis;

    /**
     * @var Editor
     */
    private $editor;

    /**
     * @var ParserPhpNameScanner
     */
    private $nameScanner;

    /**
     * @var Set
     */
    private $renames;


    public function __construct(CodeAnalysis $codeAnalysis, Editor $editor, ParserPhpNameScanner $nameScanner)
    {
        $this->codeAnalysis = $codeAnalysis;
        $this->editor = $editor;
        $this->nameScanner = $nameScanner;
    }


    public function refactor(Directory $directory)
    {
        $phpFiles = $directory->findAllPhpFilesRecursivly();

        $this->renames = new Set();

        foreach ($phpFiles as $phpFile) {
            $this->checkIfRenameIsRequired($phpFile);
        }

        $occurances = $this->findOccurances($phpFiles);

        foreach ($occurances as $occurance) {
            $this->performRename($occurance);
        }

        $this->editor->save();
    }


    private function checkIfRenameIsRequired(File $phpFile)
    {
        $classes = $this->codeAnalysis->findClasses($phpFile);

        // Why skip for multiple classes in a file?
        if (count($classes) !== 1) {
            return;
        }

        $class = $classes[0];

        $currentClassName = $class->declarationName();
        $expectedClassName = $phpFile->extractPsr0ClassName();

        if ($this->shortNameHasChanged($expectedClassName, $currentClassName)) {
            // Queue a rename to happen in the next loop
            $this->renames->add(new PhpNameChange($currentClassName, $expectedClassName));
        }

        if ($this->namespaceHasChanged($expectedClassName, $currentClassName)) {
            $this->renames->add(new PhpNameChange($currentClassName->fullyQualified(), $expectedClassName->fullyQualified()));
        }
    }

    /**
     * @return boolean
     */
    private function shortNameHasChanged(PhpName $expectedClassName, PhpName $currentClassName)
    {
        return $expectedClassName->shortName() !== $currentClassName->shortName();
    }

    /**
     * @return boolean
     */
    private function namespaceHasChanged(PhpName $expectedClassName, PhpName $currentClassName)
    {
        return !$expectedClassName->namespaceName()->equals($currentClassName->namespaceName());
    }

    private function performRename(PhpNameOccurance $occurance)
    {
        $name = $occurance->name();

        foreach ($this->renames as $rename) {
            if (!$rename->affects($name)) {
                continue;
            }

            $buffer = $this->editor->openBuffer($occurance->file());

            $buffer->replaceString(
                $occurance->declarationLine(),
                $name->relativeName(),
                $rename->change($name)->relativeName()
            );

            // Why is a contnue required? Surely 2 renames can't apply
            // to the same occurance?
            break;
        }
    }

    /**
     * @param File[] $phpFiles
     *
     * @return PhpNameOccurance[]
     */
    private function findOccurances($phpFiles)
    {
        $occurances = array();

        $noImportedUsages = new NoImportedUsagesFilter();

        foreach ($phpFiles as $phpFile) {
            $occurances = array_merge(
                $noImportedUsages->filter($this->nameScanner->findNames($phpFile)),
                $occurances
            );
        }

        return $occurances;
    }
}
