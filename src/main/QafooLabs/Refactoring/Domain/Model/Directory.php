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

namespace QafooLabs\Refactoring\Domain\Model;

use QafooLabs\Refactoring\Utils\CallbackFilterIterator;
use QafooLabs\Refactoring\Utils\CallbackTransformIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * A directory in a project.
 */
class Directory
{
    /**
     * @var string
     */
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return File[]
     */
    public function findAllPhpFilesRecursivly()
    {
        $workingPath = $this->path;

        return
            new CallbackTransformIterator(
                new CallbackFilterIterator(
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($this->path),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    ),
                    function (SplFileInfo $file) {
                        return substr($file->getFilename(), -4) === ".php";
                    }
                ),
                function ($file) use ($workingPath) {
                    return File::createFromPath($file->getPathname(), $workingPath);
                }
            )
        ;
    }
}

