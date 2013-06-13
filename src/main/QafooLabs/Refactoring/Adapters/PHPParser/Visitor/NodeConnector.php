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

use PHPParser_NodeVisitorAbstract;
use PHPParser_Node;

/**
 * Connects the nodes.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class NodeConnector extends PHPParser_NodeVisitorAbstract
{
    public function enterNode(PHPParser_Node $node)
    {
        $subNodes = array();
        foreach ($node as $subNode) {
            if ($subNode instanceof PHPParser_Node) {
                $subNodes[] = $subNode;
                continue;
            } else if (!is_array($subNode)) {
                continue;
            }

            $subNodes = array_merge($subNodes, array_values($subNode));
        }

        for ($i=0,$c=count($subNodes); $i<$c; $i++) {
            if (!$subNodes[$i] instanceof PHPParser_Node) {
                continue;
            }

            $subNodes[$i]->setAttribute('parent', $node);

            if ($i > 0) {
                $subNodes[$i]->setAttribute('previous', $subNodes[$i - 1]);
            }
            if ($i + 1 < $c) {
                $subNodes[$i]->setAttribute('next', $subNodes[$i + 1]);
            }
        }
    }
}
