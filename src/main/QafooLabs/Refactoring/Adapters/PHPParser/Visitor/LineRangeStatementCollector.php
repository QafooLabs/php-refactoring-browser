<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use PHPParser_Node;
use PHPParser_Node_Stmt;
use PHPParser_Node_Expr_FuncCall;

/**
 * Given a line range, collect the AST slice that is inside that range.
 */
class LineRangeStatementCollector extends \PHPParser_NodeVisitorAbstract
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

    public function enterNode(PHPParser_Node $node)
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

