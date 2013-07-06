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
 * Representation of a PHP class
 */
class PhpClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $declarationLine;

    /**
     * @var int
     */
    private $namespaceDeclarationLine;

    public function __construct($name, $declarationLine, $namespaceDeclarationLine)
    {
        $this->name = $name;
        $this->declarationLine = $declarationLine;
        $this->namespaceDeclarationLine = $namespaceDeclarationLine;
    }

    /**
     * PhpName for the declaration of this class.
     *
     * @return PhpName
     */
    public function declarationName()
    {
        $parts = explode("\\", $this->name);

        return new PhpName($this->name, array_pop($parts));
    }

    public function declarationLine()
    {
        return $this->declarationLine;
    }

    public function namespaceDeclarationLine()
    {
        return $this->namespaceDeclarationLine;
    }
}

