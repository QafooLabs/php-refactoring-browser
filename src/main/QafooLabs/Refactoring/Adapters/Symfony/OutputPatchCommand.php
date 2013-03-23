<?php

namespace QafooLabs\Refactoring\Adapters\Symfony;

use Symfony\Component\Console\Output\OutputInterface;
use QafooLabs\Refactoring\Adapters\Patches\ApplyPatchCommand;

/**
 * Print Patch to Symfony Console Output
 */
class OutputPatchCommand implements ApplyPatchCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @var string
     */
    public function apply($patch)
    {
        $this->output->writeln($patch);
    }
}
