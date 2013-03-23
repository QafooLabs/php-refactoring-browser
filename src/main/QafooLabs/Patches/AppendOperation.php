<?php

namespace QafooLabs\Patches;

class AppendOperation implements Operation
{
    private $originalLine;
    private $appendLines;

    public function __construct($originalLine, $appendLines)
    {
        $this->originalLine = $originalLine;
        $this->appendLines = $appendLines;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->appendLines($this->originalLine, $this->appendLines);
    }
}
