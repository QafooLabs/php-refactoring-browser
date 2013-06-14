<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\File;

interface UseStatementScanner
{
    /**
     * Find all use statements in the file.
     *
     * @param File $file
     * @return PhpUseStatement[]
     */
    public function findUseStatements(File $file);
}
