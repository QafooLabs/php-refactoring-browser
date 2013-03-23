<?php

namespace QafooLabs\Refactoring\Adapters\Patches;

use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Patches\PatchBuilder;

class PatchBuffer implements EditorBuffer
{
    /**
     * @var \QafooLabs\Patches\PatchBuilder
     */
    private $builder;

    public function __construct(PatchBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function replace(LineRange $range, array $newLines)
    {
        $this->builder->replaceLines($range->getStart(), $range->getEnd(), $newLines);
    }

    public function append($line, array $newLines)
    {
        $this->builder->appendToLine($line, $newLines);
    }
}
