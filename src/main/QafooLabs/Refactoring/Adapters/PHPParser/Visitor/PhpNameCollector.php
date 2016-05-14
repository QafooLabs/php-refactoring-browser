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

/**
 * Visitor for PHP Parser collecting PHP Names from an AST.
 */
class PhpNameCollector extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $nameDeclarations = array();
    /**
     * @var array
     */
    private $useStatements = array();
    /**
     * @var string
     */
    private $currentNamespace;

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                if ($use instanceof Node\Stmt\UseUse) {
                    $name = implode('\\', $use->name->parts);

                    $this->useStatements[$use->alias] = $name;
                    $this->nameDeclarations[] = array(
                        'alias' => $name,
                        'fqcn' => $name,
                        'line' => $use->getLine(),
                        'type' => 'use',
                    );
                }
            }
        }


        if ($node instanceof Node\Expr\New_ && $node->class instanceof Node\Name) {
            $usedAlias = implode('\\', $node->class->parts);

            $this->nameDeclarations[] = array(
                'alias' => $usedAlias,
                'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $node->class->isFullyQualified()),
                'line' => $node->getLine(),
                'type' => 'usage',
            );
        }

        if ($node instanceof Node\Expr\StaticCall && $node->class instanceof Node\Name) {
            $usedAlias = implode('\\', $node->class->parts);

            $this->nameDeclarations[] = array(
                'alias' => $usedAlias,
                'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $node->class->isFullyQualified()),
                'line' => $node->getLine(),
                'type' => 'usage',
            );
        }

        if ($node instanceof Node\Stmt\Class_) {
            $className = $node->name;

            $this->nameDeclarations[] = array(
                'alias' => $className,
                'fqcn' => $this->fullyQualifiedNameFor($className, false),
                'line' => $node->getLine(),
                'type' => 'class',
            );

            if ($node->extends) {
                $usedAlias = implode('\\', $node->extends->parts);

                $this->nameDeclarations[] = array(
                    'alias' => $usedAlias,
                    'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $node->extends->isFullyQualified()),
                    'line' => $node->extends->getLine(),
                    'type' => 'usage',
                );
            }

            foreach ($node->implements as $implement) {
                $usedAlias = implode('\\', $implement->parts);

                $this->nameDeclarations[] = array(
                    'alias' => $usedAlias,
                    'fqcn' => $this->fullyQualifiedNameFor($usedAlias, $implement->isFullyQualified()),
                    'line' => $implement->getLine(),
                    'type' => 'usage',
                );
            }
        }

        if ($node instanceof Node\Stmt\Namespace_) {
            $this->currentNamespace = implode('\\', $node->name->parts);
            $this->useStatements = array();

            $this->nameDeclarations[] = array(
                'alias' => $this->currentNamespace,
                'fqcn' => $this->currentNamespace,
                'line' => $node->name->getLine(),
                'type' => 'namespace',
            );
        }
    }

    private function fullyQualifiedNameFor($alias, $isFullyQualified)
    {
        $isAbsolute = $alias[0] === "\\";

        if ($isAbsolute || $isFullyQualified) {
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
