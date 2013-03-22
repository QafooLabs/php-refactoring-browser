<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class ChangeOperation implements Operation
{
    private $newLine;

    public function __construct($newLine)
    {
        $this->newLine = $newLine;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->changeLines($this->newLine);
    }
}
