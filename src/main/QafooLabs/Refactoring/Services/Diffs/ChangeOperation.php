<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class ChangeOperation implements Operation
{
    private $newLine;

    public function __construct($newLine)
    {
        $this->newLine = $newLine;
    }

    public function perform(array $lines)
    {
        return array_merge(
            array(
                '-' . ltrim($lines[0]),
                '+' . $this->newLine,
            ),
            array_slice($lines, 1)
        );
    }
}
