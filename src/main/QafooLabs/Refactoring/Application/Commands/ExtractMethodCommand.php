<?php

namespace QafooLabs\Refactoring\Application\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

class ExtractMethodCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('extract-method')
            ->setDescription('Extract a list of statements into a method.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains list of statements to extract')
            ->addArgument('range', InputArgument::REQUIRED, 'Line Range of statements that should be extracted.')
            ->addArgument('newmethod', InputArgument::REQUIRED, 'Name of the new method.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
    }
}
