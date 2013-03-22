<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class RemoveOperation implements Operation
{
    public function perform(array $lines)
    {
        return array('-' . ltrim($lines[0]));
    }
}
