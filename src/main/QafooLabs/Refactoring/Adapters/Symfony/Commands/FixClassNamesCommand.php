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

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use Symfony\Component\Console\Input\InputArgument;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use QafooLabs\Refactoring\Application\FixClassNames;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;
use QafooLabs\Refactoring\Domain\Model\Directory;

class FixClassNamesCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('fix-class-names')
            ->setDescription('Find all classes whose names don\'t match their required PSR-0 name and rename them.')
            ->addArgument('dir', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Directory that contains the source code to refactor')
            ->setHelp(<<<HELP
Fix class and namespace names to correspond to the current filesystem layout,
given that the project uses PSR-0. This means you can use this tool to
rename classes and namespaces by renaming folders and files and then applying
the command to fix class and namespaces.

<comment>Operations:</comment>

1. Find all PHP files in given directory.
2. Check every PHP file for class names and namespace definition
3. Change the namespaces and class names to match the current file name

<comment>Pre-Conditions:</comment>

This refactoring has no pre-conditions.

<comment>Usage:</comment>

    <info>php refactor.phar fix-class-names src/</info>
HELP
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = new Directory($input->getArgument('dir'), getcwd());

        $codeAnalysis = new StaticCodeAnalysis();
        $phpNameScanner = new ParserPhpNameScanner();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $fixClassNames = new FixClassNames($codeAnalysis, $editor, $phpNameScanner);
        $fixClassNames->refactor($directory);
    }
}

