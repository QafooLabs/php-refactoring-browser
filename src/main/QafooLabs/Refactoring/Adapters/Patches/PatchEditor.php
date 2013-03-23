<?php

namespace QafooLabs\Refactoring\Adapters\Patches;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Services\Editor;
use QafooLabs\Patches\PatchBuilder;

/**
 * Editor creates patches for all changes.
 */
class PatchEditor implements Editor
{
    private $builders = array();
    private $command;

    public function __construct(ApplyPatchCommand $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritDoc}
     */
    public function openBuffer(File $file)
    {
        if ( ! isset($this->builders[$file->getRelativePath()])) {
            $this->builders[$file->getRelativePath()] = new PatchBuilder(
                $file->getCode(), $file->getRelativePath()
            );
        }

        return new PatchBuffer($this->builders[$file->getRelativePath()]);
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        foreach ($this->builders as $builder) {
            $this->command->apply($builder->generateUnifiedDiff());
        }
    }
}
