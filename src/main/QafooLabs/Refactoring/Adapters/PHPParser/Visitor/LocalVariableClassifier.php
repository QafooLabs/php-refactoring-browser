<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PHPParser_Node;

/**
 * Classify local variables into assignments and usages,
 * permanent and temporary variables.
 */
class LocalVariableClassifier extends \PHPParser_NodeVisitorAbstract
{
    private $localVariables = array();
    private $assignments = array();

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Expr_Variable) {
            $this->enterVariableNode($node);
        }

        if ($node instanceof \PHPParser_Node_Expr_Assign) {
            $this->enterAssignment($node);
        }
    }

    private function enterAssignment($node)
    {
        if ($node->var instanceof \PHPParser_Node_Expr_Variable) {
            $this->assignments[] = $node->var->name;
        }
    }

    private function enterVariableNode($node)
    {
        if ($node->name === "this") {
            return;
        }

        $this->localVariables[] = $node->name;
    }

    public function getLocalVariables()
    {
        return $this->localVariables;
    }

    public function getUsedLocalVariables()
    {
        // TODO: wrong if usage before assignment
        return array_diff($this->localVariables, $this->assignments);
    }

    public function getAssignments()
    {
        return $this->assignments;
    }
}
