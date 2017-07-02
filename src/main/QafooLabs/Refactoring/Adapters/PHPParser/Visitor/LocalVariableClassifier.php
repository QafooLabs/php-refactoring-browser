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
 * Classify local variables into assignments and usages,
 * permanent and temporary variables.
 */
class LocalVariableClassifier extends NodeVisitorAbstract
{
    private $localVariables = array();
    private $assignments = array();
    private $seenAssignmentVariables;

    public function __construct()
    {
        $this->seenAssignmentVariables = new SplObjectStorage();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\Variable) {
            $this->enterVariableNode($node);
        }

        if ($node instanceof Node\Expr\Assign) {
            $this->enterAssignment($node);
        }

        if ($node instanceof Node\Param) {
            $this->enterParam($node);
        }
    }

    private function enterParam(Node\Param $node)
    {
        $this->assignments[$node->name][] = $node->getLine();
    }

    private function enterAssignment(Node\Expr\Assign $node)
    {
        if ($node->var instanceof Node\Expr\Variable) {
            $this->assignments[$node->var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($node->var);
        } else if ($node->var instanceof Node\Expr\ArrayDimFetch) {
            // unfold $array[$var][$var]
            $var = $node->var->var;
            while (!isset($var->name)) {
                $var = $var->var;
            }
            // $foo[] = "baz" is both a read and a write access to $foo
            $this->localVariables[$var->name][] = $node->getLine();
            $this->assignments[$var->name][] = $node->getLine();
            $this->seenAssignmentVariables->attach($var);
        }
    }

    private function enterVariableNode(Node\Expr\Variable $node)
    {
        if ($node->name === 'this' || $this->seenAssignmentVariables->contains($node)) {
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
