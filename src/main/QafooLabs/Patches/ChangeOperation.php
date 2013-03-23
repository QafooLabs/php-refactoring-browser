<?php

namespace QafooLabs\Patches;

class ChangeOperation implements Operation
{
    private $originalLine;
    private $newLines;

    public function __construct($originalLine, array $newLines)
    {
        $this->originalLine = $originalLine;
        $this->newLines = $newLines;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->changeLines($this->originalLine, $this->newLines);
    }
}
