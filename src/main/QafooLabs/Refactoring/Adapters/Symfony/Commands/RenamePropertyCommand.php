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

use QafooLabs\Refactoring\Application\RenameProperty;
use QafooLabs\Refactoring\Domain\Model;

use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;

class RenamePropertyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('rename-property')
            ->setDescription('Rename a class property.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains the class')
            ->addArgument('line', InputArgument::REQUIRED, 'Line where the property is defined or used.')
            ->addArgument('name', InputArgument::REQUIRED, 'Current name of the property without the "$this->"')
            ->addArgument('new-name', InputArgument::REQUIRED, 'New name of the property')
            ->setHelp(<<<HELP
Rename a class property.

<comment>Operations:</comment>

1. Renames a property by giving it a new name inside the class.

<comment>Pre-Conditions:</comment>

1. Check that new property name does not exist (NOT YET CHECKED).

<comment>Usage:</comment>

    <info>php refactor.phar rename-property file.php 17 hello newHello</info>

Renames <info>\$hello</info> in line <info>17</info> of <info>file.php</info> into <info>\$newHello</info>.

HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = Model\File::createFromPath($input->getArgument('file'), getcwd());
        $line = (int)$input->getArgument('line');
        $name = new Model\Variable($input->getArgument('name'));
        $newName = new Model\Variable($input->getArgument('new-name'));

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $renameLocalVariable = new RenameProperty($scanner, $codeAnalysis, $editor);
        $renameLocalVariable->refactor($file, $line, $name, $newName);
    }
}
