<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;

/**
 * CodeAnalysis provider
 */
interface CodeAnalysis
{
    /**
     * Is the method in the given line range static?
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return bool
     */
    public function isMethodStatic(File $file, LineRange $range);

    /**
     * Get the method start line
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return int
     */
    public function getMethodStartLine(File $file, LineRange $range);

    /**
     * Get the method end line
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return int
     */
    public function getMethodEndLine(File $file, LineRange $range);

    /**
     * @param File $file
     * @param int $line
     */
    public function getLineOfLastPropertyDefinedInScope(File $file, $line);
}

