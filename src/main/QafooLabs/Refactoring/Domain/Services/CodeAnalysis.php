<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;

interface CodeAnalysis
{
    public function isMethodStatic(File $file, LineRange $range);
    public function getMethodEndLine(File $file, LineRange $range);
}
