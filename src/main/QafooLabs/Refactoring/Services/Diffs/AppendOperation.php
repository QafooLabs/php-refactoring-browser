<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class AppendOperation implements Operation
{
    private $appendLines;

    public function __construct($appendLines)
    {
        $this->appendLines = $appendLines;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->appendLines($this->appendLines);
    }
}
