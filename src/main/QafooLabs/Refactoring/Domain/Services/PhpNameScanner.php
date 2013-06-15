<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\File;

interface PhpNameScanner
{
    /**
     * Find all php names in the file.
     *
     * @param File $file
     * @return PhpName[]
     */
    public function findNames(File $file);
}
