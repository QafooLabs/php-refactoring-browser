<?php

namespace QafooLabs\Patches;

class ChangeTokenOperation implements Operation
{
    private $originalLine;
    private $oldToken;
    private $newToken;

    public function __construct($originalLine, $oldToken, $newToken)
    {
        $this->originalLine = $originalLine;
        $this->oldToken = $oldToken;
        $this->newToken = $newToken;
    }

    public function perform(Hunk $hunk)
    {
        return $hunk->changeToken($this->originalLine, $this->oldToken, $this->newToken);
    }
}
