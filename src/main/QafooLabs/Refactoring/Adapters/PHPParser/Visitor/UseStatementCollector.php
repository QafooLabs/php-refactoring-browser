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
use PHPParser_Node_Stmt_Use;
use PHPParser_Node_Stmt_UseUse;

class UseStatementCollector extends \PHPParser_NodeVisitorAbstract
{
    private $useDeclarations;

    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof PHPParser_Node_Stmt_UseUse) {
            $this->useDeclarations[] = array(
                'name' => implode('\\', $node->name->parts),
                'line' => $node->getLine()
            );
        }
    }

    public function collectedUseDeclarations()
    {
        return $this->useDeclarations;
    }
}
