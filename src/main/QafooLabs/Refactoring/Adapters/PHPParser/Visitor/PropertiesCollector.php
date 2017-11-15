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


namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SplObjectStorage;

/**
 * Collects class properties definitions and usages.
 */
class PropertiesCollector extends NodeVisitorAbstract
{
    private $usages = array();
    private $declarations = array();

    private $seenPropertyUsages;

    public function __construct()
    {
        $this->seenPropertyUsages = new SplObjectStorage();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Property) {
            $this->declarations[$node->props[0]->name] = $node->getLine();
        }

        if ($node instanceof Node\Expr\PropertyFetch && !$this->seenPropertyUsages->contains($node)) {
            $this->usages[$node->name][] = $node->getLine();
            $this->seenPropertyUsages->attach($node);
        }
    }

    public function getDeclarations()
    {
        return $this->declarations;
    }

    public function getUsages()
    {
        return $this->usages;
    }
}
