<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Buffer of the Editor that is currently connected to the RefactoringBrowser
 */
interface EditorBuffer
{
    public function replace(LineRange $range, array $newLines);
    public function append($line, array $newLines);
}
