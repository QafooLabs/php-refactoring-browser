<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;

interface VariableScanner
{
    public function scanForVariables(File $file, LineRange $range);
}
