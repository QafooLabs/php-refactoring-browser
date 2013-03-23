<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Buffer of the Editor that is currently connected to the RefactoringBrowser
 */
interface EditorBuffer
{
    /**
     * Replace LineRange with new lines.
     *
     * @param LineRange $range
     * @param array $newLines
     *
     * @return void
     */
    public function replace(LineRange $range, array $newLines);

    /**
     * Append new lines to a given line.
     *
     * @param integer $line
     * @param array $newLines
     *
     * @return void
     */
    public function append($line, array $newLines);

    /**
     * Replace a token in in a line with another token.
     *
     * @param integer $line
     * @param string $oldToken
     * @param string $newToken
     *
     * @return void
     */
    public function replaceString($line, $oldToken, $newToken);
}

