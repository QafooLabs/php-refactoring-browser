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
        $assignments = $this->assignments;

        foreach ($this->localVariables as $localVariable => $lines) {
            if (isset($assignments[$localVariable])) {
                $assignments[$localVariable] = array_unique($lines);
            }
        }

        return $assignments;
    }
}
