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
use PHPParser_Node_Name;
use PHPParser_Node_Stmt_Namespace;
use PHPParser_Node_Stmt_Use;
use PHPParser_Node_Stmt_Class;
use PHPParser_Node_Stmt_UseUse;
use PHPParser_Node_Expr_New;
use PHPParser_Node_Expr_StaticCall;

class PhpNameCollector extends \PHPParser_NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $nameDeclarations = array();
    private $useStatements = array();
    private $currentNamespace;

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_UseUse) {
            $name = implode('\\', $node->name->parts);

            $this->useStatements[$node->alias] = $name;
            $this->nameDeclarations[] = array(
                'alias' => $name,
                'fqcn' => $name,
                'line' => $node->getLine()
            );
        }

        if ($node instanceof PHPParser_Node_Expr_New && $node->class instanceof PHPParser_Node_Name) {
            $usedAlias = implode('\\', $node->class->parts);

            $this->nameDeclarations[] = array(
                'alias' => $usedAlias,
                'fqcn' => $this->fullyQualifiedNameFor($usedAlias),
                'line' => $node->getLine(),
            );
        }

        if ($node instanceof PHPParser_Node_Expr_StaticCall && $node->class instanceof PHPParser_Node_Name) {
            $usedAlias = implode('\\', $node->class->parts);

            $this->nameDeclarations[] = array(
                'alias' => $usedAlias,
                'fqcn' => $this->fullyQualifiedNameFor($usedAlias),
                'line' => $node->getLine(),
            );
        }

        if ($node instanceof PHPParser_Node_Stmt_Class) {
            $className = $node->name;

            $this->nameDeclarations[] = array(
                'alias' => $className,
                'fqcn' => $this->fullyQualifiedNameFor($className),
                'line' => $node->getLine()
            );

            if ($node->extends) {
                $usedAlias = implode('\\', $node->extends->parts);

                $this->nameDeclarations[] = array(
                    'alias' => $usedAlias,
                    'fqcn' => $this->fullyQualifiedNameFor($usedAlias),
                    'line' => $node->extends->getLine(),
                );
            }

            foreach ($node->implements as $implement) {
                $usedAlias = implode('\\', $implement->parts);

                $this->nameDeclarations[] = array(
                    'alias' => $usedAlias,
                    'fqcn' => $this->fullyQualifiedNameFor($usedAlias),
                    'line' => $implement->getLine(),
                );
            }
        }

        if ($node instanceof PHPParser_Node_Stmt_Namespace) {
            $this->currentNamespace = implode('\\', $node->name->parts);
            $this->useStatements = array();

            $this->nameDeclarations[] = array(
                'alias' => $this->currentNamespace,
                'fqcn' => $this->currentNamespace,
                'line' => $node->name->getLine()
            );
        }
    }

    private function fullyQualifiedNameFor($alias)
    {
        $isAbsolute = $alias[0] === "\\";

        if ($isAbsolute) {
            $class = $alias;
        } else if (isset($this->useStatements[$alias])) {
            $class = $this->useStatements[$alias];
        } else {
            $class = ltrim($this->currentNamespace . '\\' . $alias, '\\');
        }

        return $class;
    }

    public function collectedNameDeclarations()
    {
        return $this->nameDeclarations;
    }
}
