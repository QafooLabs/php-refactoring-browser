<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PHPParser_Node;
use PHPParser_NodeVisitorAbstract;
use PHPParser_Node_Expr_Variable;
use PHPParser_Node_Expr_Assign;

/**
 * Classify local variables into assignments and usages,
 * permanent and temporary variables.
 */
class LocalVariableClassifier extends PHPParser_NodeVisitorAbstract
{
    private $localVariables = array();
    private $assignments = array();

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Expr_Variable) {
            $this->enterVariableNode($node);
        }

        if ($node instanceof PHPParser_Node_Expr_Assign) {
            $this->enterAssignment($node);
        }
    }

    private function enterAssignment($node)
    {
        if ($node->var instanceof PHPParser_Node_Expr_Variable) {
            $this->assignments[$node->var->name][] = $node->getLine();
        }
    }

    private function enterVariableNode($node)
    {
        if ($node->name === "this") {
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
        $usedLocalVariables = $this->localVariables;

        foreach ($this->assignments as $assignmentName => $_) {
            if (min($usedLocalVariables[$assignmentName]) >= min($this->assignments[$assignmentName])) {
                unset($usedLocalVariables[$assignmentName]);
            }
        }

        return $usedLocalVariables;
    }

    public function getAssignments()
    {
        return $this->assignments;
    }
}
