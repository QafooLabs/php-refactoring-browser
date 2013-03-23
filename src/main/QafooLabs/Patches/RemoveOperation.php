<?php

namespace QafooLabs\Patches;

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
