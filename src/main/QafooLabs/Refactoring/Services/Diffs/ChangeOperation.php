<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class ChangeOperation implements Operation
{
    private $originalLine;
    private $newLine;

    public function __construct($originalLine, $newLine)
    {
        $this->originalLine = $originalLine;
        $this->newLine = $newLine;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->changeLine($this->originalLine, $this->newLine);
    }
}
