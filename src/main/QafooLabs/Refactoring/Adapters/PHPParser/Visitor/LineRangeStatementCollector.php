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
use QafooLabs\Refactoring\Domain\Model\LineRange;

/**
 * Given a line range, collect the AST slice that is inside that range.
 */
class LineRangeStatementCollector extends NodeVisitorAbstract
{
    /**
     * @var LineRange
     */
    private $lineRange;
    private $statements;

    public function __construct(LineRange $lineRange)
    {
        $this->lineRange = $lineRange;
        $this->statements = new \SplObjectStorage();
    }

    public function enterNode(Node $node)
    {
        if ( ! $this->lineRange->isInRange($node->getLine())) {
            return;
        }

        $parent = $node->getAttribute('parent');

        // TODO: Expensive (?)
        do {
            if ($parent && $this->statements->contains($parent)) {
                return;
            }
        } while($parent && $parent = $parent->getAttribute('parent'));

        $this->statements->attach($node);
    }

    public function getStatements()
    {
        return iterator_to_array($this->statements);
    }
}

