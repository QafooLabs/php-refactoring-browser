<?php

namespace QafooLabs\Refactoring\Adapters\Symfony\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use QafooLabs\Refactoring\Domain\Model\File;

class RenameLocalVariableCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('rename-local-variable')
            ->setDescription('Rename a local variable inside a method')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains list of statements to extract')
            ->addArgument('line', InputArgument::REQUIRED, 'Line where the local variable is defined.')
            ->addArgument('name', InputArgument::REQUIRED, 'Current name of the variable, with or without the $')
            ->addArgument('new-name', InputArgument::REQUIRED, 'New name of the variable')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = File::createFromPath($input->getArgument('file'), getcwd());
        $line = $input->getArgument('line');
        $name = $input->getArgument('name');
        $newName = $input->getArgument('new-name');
    }
}
