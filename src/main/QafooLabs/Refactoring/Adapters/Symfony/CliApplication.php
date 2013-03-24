<?php

namespace QafooLabs\Refactoring\Adapters\Symfony;

use Symfony\Component\Console\Application;

class CliApplication extends Application
{
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Commands\ExtractMethodCommand();
        $commands[] = new Commands\RenameLocalVariableCommand();
        $commands[] = new Commands\ConvertLocalToInstanceVariableCommand();

        return $commands;
    }
}

