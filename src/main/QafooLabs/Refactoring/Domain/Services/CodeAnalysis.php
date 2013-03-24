<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;

/**
 * CodeAnalysis provider
 */
abstract class CodeAnalysis
{
    /**
     * Is the method in the given line range static?
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return bool
     */
    abstract public function isMethodStatic(File $file, LineRange $range);

    /**
     * Get the method start line
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return int
     */
    abstract public function getMethodStartLine(File $file, LineRange $range);

    /**
     * Get the method end line
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return int
     */
    abstract public function getMethodEndLine(File $file, LineRange $range);

    /**
     * @param File $file
     * @param int $line
     */
    abstract public function getLineOfLastPropertyDefinedInScope(File $file, $line);
    /**
     * @param File $file
     * @param integer $line
     *
     * @return LineRange
     */
    public function findMethodRange(File $file, $line)
    {
        $range = LineRange::fromSingleLine($line);
        $methodStartLine = $this->getMethodStartLine($file, $range);
        $methodEndLine = $this->getMethodEndLine($file, $range);

        return LineRange::fromLines($methodStartLine, $methodEndLine);
    }
}

