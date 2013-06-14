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

class PhpUseStatement
{
    private $file;
    private $declaration;
    private $line;

    public function __construct(File $file, $declaration, $line)
    {
        $this->file = $file;
        $this->declaration = $declaration;
        $this->line = $line;
    }

    /**
     * @param string
     * @return bool
     */
    public function isForClass($name)
    {
        return ltrim($name, '\\') === ltrim($this->declaration, '\\');
    }

    public function startsWithNamespace($namespace)
    {
        return strpos(ltrim($this->declaration, '\\'), ltrim($namespace, '\\')) === 0;
    }

    /**
     * @return File
     */
    public function file()
    {
        return $this->file;
    }

    public function line()
    {
        return $this->line;
    }
}
