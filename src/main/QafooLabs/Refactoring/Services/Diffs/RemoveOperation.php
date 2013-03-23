<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class RemoveOperation implements Operation
{
    private $originalLine;

    public function __construct($originalLine)
    {
        $this->originalLine = $originalLine;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->removeLine($this->originalLine);
    }
}
