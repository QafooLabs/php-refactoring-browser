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

use PHPParser_Node;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Node_Expr_Variable;
use PHPParser_Node_Expr_Assign;
use PHPParser_Node_Expr_ArrayDimFetch;
use PHPParser_Node_Param;
use SplObjectStorage;

/**
 * Classify local variables into assignments and usages,
 * permanent and temporary variables.
 */
class LocalVariableClassifier extends PHPParser_NodeVisitorAbstract
{
    private $localVariables = array();
    private $assignments = array();
    private $seenAssignmentVariables;

    public function __construct()
    {
        $this->seenAssignmentVariables = new SplObjectStorage();
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_Variable) {
            $this->enterVariableNode($node);
        }

        if ($node instanceof PHPParser_Node_Expr_Assign) {
            $this->enterAssignment($node);
        }

        if ($node instanceof PHPParser_Node_Param) {
            $this->enterParam($node);
        }
    }

    private function enterParam($node)
    {
        $this->assignments[$node->name][] = $node->getLine();
    }

    private function enterAssignment($node)
    {
        if ($node->var instanceof PHPParser_Node_Expr_Variable) {
            $this->assignments[$node->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var);
        } else if ($node->var instanceof PHPParser_Node_Expr_ArrayDimFetch) {
            // $foo[] = "baz" is both a read and a write access to $foo
            $this->localVariables[$node->var->var->name][] = $node->getLine();
            $this->assignments[$node->var->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var->var);
        }
    }

    private function enterVariableNode($node)
    {
        if ($node->name === "this" || $this->seenAssignmentVariables->contains($node)) {
            return;
        }

        $this->localVariables[$node->name][] = $node->getLine();
    }

    public function getLocalVariables()
    {
        return $this->localVariables;
    }

    public function getUsedLocalVariables()
    {
        return $this->localVariables;
    }

    public function getAssignments()
    {
        return $this->assignments;
    }
}
