<?php

namespace QafooLabs\Refactoring\Services\Diffs;

interface Operation
{
    public function perform(array $lines);
}
