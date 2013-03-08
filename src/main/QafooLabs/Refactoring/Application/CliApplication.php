<?php

namespace QafooLabs\Refactoring\Application;

use Symfony\Component\Console\Application;

class CliApplication extends Application
{
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Commands\ExtractMethodCommand();

        return $commands;
    }
}

