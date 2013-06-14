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
        if (empty($patch)) {
            return;
        }

        $this->output->writeln($patch);
    }
}
