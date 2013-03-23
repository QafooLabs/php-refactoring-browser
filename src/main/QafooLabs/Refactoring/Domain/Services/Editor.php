<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\File;

interface Editor
{
    /**
     * Open an EditorBuffer where you can change the contents of a file.
     *
     * @return EditorBuffer
     */
    public function openBuffer(File $file);

    /**
     * Save all buffers.
     *
     * @return void
     */
    public function save();
}
