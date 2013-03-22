<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class RemoveOperation implements Operation
{
    public function perform(Hunk $hunk)
    {
        return $hunk->removeLines();
    }
}
