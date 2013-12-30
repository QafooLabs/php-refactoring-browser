<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */


namespace QafooLabs\Refactoring\Adapters\PatchBuilder;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Services\Editor;
use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchBuilder;

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
