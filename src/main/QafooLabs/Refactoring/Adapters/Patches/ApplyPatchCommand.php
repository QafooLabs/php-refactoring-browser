<?php

namespace QafooLabs\Refactoring\Adapters\Patches;

interface ApplyPatchCommand
{
    /**
     * @var string
     */
    public function apply($patch);
}
