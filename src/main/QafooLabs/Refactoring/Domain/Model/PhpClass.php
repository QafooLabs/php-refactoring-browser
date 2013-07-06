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
     * @var PhpName
     */
    private $declarationName;

    /**
     * @var int
     */
    private $declarationLine;

    /**
     * @var int
     */
    private $namespaceDeclarationLine;

    public function __construct(PhpName $declarationName, $declarationLine, $namespaceDeclarationLine)
    {
        $this->declarationName = $declarationName;
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
        return $this->declarationName;
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

