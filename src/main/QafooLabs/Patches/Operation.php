<?php

namespace QafooLabs\Patches;

interface Operation
{
    /**
     * Perform opertaion on the hunk and return a new modified hunk.
     *
     * @param Hunk $hunk
     *
     * @return Hunk
     */
    public function perform(Hunk $hunk);
}
