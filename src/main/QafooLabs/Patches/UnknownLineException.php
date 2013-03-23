<?php

namespace QafooLabs\Patches;

use InvalidArgumentException;

class UnknownLineException extends InvalidArgumentException
{
    public function __construct($line)
    {
        parent::__construct(sprintf('Accessed non existing line %d in code.', $line));
    }
}
