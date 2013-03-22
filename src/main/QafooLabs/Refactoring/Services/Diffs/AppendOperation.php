<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class AppendOperation implements Operation
{
    private $appendLines;

    public function __construct($appendLines)
    {
        $this->appendLines = $appendLines;
    }

    public function perform(array $lines)
    {
        return array_merge(
            $lines,
            array_map(function($line) {
                return '+' . $line;
            }, $this->appendLines));
    }
}
