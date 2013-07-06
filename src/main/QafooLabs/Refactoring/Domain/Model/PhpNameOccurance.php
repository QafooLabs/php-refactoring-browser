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

/**
 * Occurance of a name in a specific file+line.
 */
class PhpNameOccurance
{
    /**
     * @var PhpName
     */
    private $name;
    /**
     * @var File
     */
    private $file;
    /**
     * @var int
     */
    private $declarationLine;

    public function __construct(PhpName $name, File $file, $declarationLine)
    {
        $this->name = $name;
        $this->file = $file;
        $this->declarationLine = $declarationLine;
    }

    public function name()
    {
        return $this->name;
    }

    public function declarationLine()
    {
        return $this->declarationLine;
    }

    public function file()
    {
        return $this->file;
    }
}
