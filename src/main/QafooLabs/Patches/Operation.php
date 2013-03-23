<?php

namespace QafooLabs\Patches;

interface Operation
{
    public function perform(Hunk $hunk);
}
