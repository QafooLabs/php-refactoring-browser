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
use AppendIterator;

/**
 * A directory in a project.
 */
class Directory
{
    /**
     * @var array
     */
    private $paths;

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct($paths, $workingDirectory)
    {
        if (is_string($paths)) {
            $paths = array($paths);
        }

        $this->paths = $paths;
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @return File[]
     */
    public function findAllPhpFilesRecursivly()
    {
        $workingDirectory = $this->workingDirectory;

        $iterator = new AppendIterator;

        foreach ($this->paths as $path) {
            $iterator->append(
                new CallbackTransformIterator(
                    new CallbackFilterIterator(
                        new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($path),
                            RecursiveIteratorIterator::LEAVES_ONLY
                        ),
                        function (SplFileInfo $file) {
                            return substr($file->getFilename(), -4) === ".php";
                        }
                    ),
                    function ($file) use ($workingDirectory) {
                        return File::createFromPath($file->getPathname(), $workingDirectory);
                    }
                )
            );
        }

        return $iterator;
    }
}

