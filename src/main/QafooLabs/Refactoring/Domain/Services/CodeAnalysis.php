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
     * Check if the line range is inside exactly one class method.
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return bool
     */
    abstract public function isInsideMethod(File $file, LineRange $range);

    /**
     * Find all classes in the file.
     *
     * @param File $file
     * @return PhpClass[]
     */
    abstract public function findClasses(File $file);

    /**
     * Find all use statements in the file.
     *
     * @param File $file
     * @return PhpUseStatement[]
     */
    abstract public function findUseStatements(File $file);

    /**
     * From a range within a method, find the start and end range of that method.
     *
     * @param File $file
     * @param LineRange $range
     *
     * @return LineRange
     */
    public function findMethodRange(File $file, LineRange $range)
    {
        $methodStartLine = $this->getMethodStartLine($file, $range);
        $methodEndLine = $this->getMethodEndLine($file, $range);

        return LineRange::fromLines($methodStartLine, $methodEndLine);
    }
}

