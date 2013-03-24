<?php

namespace QafooLabs\Refactoring\Adapters\Symfony;

use Symfony\Component\Console\Application;
use QafooLabs\Refactoring\Version;

class CliApplication extends Application
{
    private $logo = "
______      __           _             _              ______
| ___ \    / _|         | |           (_)             | ___ \
| |_/ /___| |_ __ _  ___| |_ ___  _ __ _ _ __   __ _  | |_/ /_ __ _____      _____  ___ _ __
|    // _ \  _/ _` |/ __| __/ _ \| '__| | '_ \ / _` | | ___ \ '__/ _ \ \ /\ / / __|/ _ \ '__|
| |\ \  __/ || (_| | (__| || (_) | |  | | | | | (_| | | |_/ / | | (_) \ V  V /\__ \  __/ |
\_| \_\___|_| \__,_|\___|\__\___/|_|  |_|_| |_|\__, | \____/|_|  \___/ \_/\_/ |___/\___|_|
                                                __/ |
                                               |___/
    ";

    public function __construct()
    {
        parent::__construct('PHP Refactoring Browser', Version::VERSION);
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Commands\ExtractMethodCommand();
        $commands[] = new Commands\RenameLocalVariableCommand();
        $commands[] = new Commands\ConvertLocalToInstanceVariableCommand();

        return $commands;
    }

    public function getHelp()
    {
        return $this->logo . parent::getHelp();
    }
}

