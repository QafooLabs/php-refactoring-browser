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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\Variable;

use QafooLabs\Refactoring\Application\ConvertLocalToInstanceVariable;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;
use QafooLabs\Refactoring\Adapters\Symfony\OutputPatchCommand;

class ConvertLocalToInstanceVariableCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('convert-local-to-instance-variable')
            ->setDescription('Convert a local variable to an instance variable.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains the local variable.')
            ->addArgument('line', InputArgument::REQUIRED, 'Line of one of the local variables occurances.')
            ->addArgument('variable', InputArgument::REQUIRED, 'Name of the variable with or without $.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $line = (int)$input->getArgument('line');
        $variable = new Variable($input->getArgument('variable'));

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor(new OutputPatchCommand($output));

        $convertRefactoring = new ConvertLocalToInstanceVariable($scanner, $codeAnalysis, $editor);
        $convertRefactoring->refactor($file, $line, $variable);
    }
}
