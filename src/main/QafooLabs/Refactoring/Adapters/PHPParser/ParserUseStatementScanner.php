<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Domain\Services\UseStatementScanner;
use QafooLabs\Refactoring\Domain\Model\File;

class ParserUseStatementScanner implements UseStatementScanner
{
    public function findUseStatements(File $file)
    {
        return array();
    }
}
