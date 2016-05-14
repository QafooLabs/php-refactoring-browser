<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_NodeTraverser;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\NodeConnector;

class LineRangeStatementCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function givenNestedStatements_WhenCollecting_ThenOnlyCollectTopLevel()
    {
        $stmts = $this->statements('$this->foo(bar(baz()));');

        $collector = new LineRangeStatementCollector($this->range('2-2'));

        $this->traverse($stmts, $collector);

        $collectedStatements = $collector->getStatements();

        $this->assertCount(1, $collectedStatements);
        $this->assertInstanceOf('PHPParser_Node_Expr_MethodCall', $collectedStatements[0]);
    }

    private function traverse($stmts, $visitor)
    {
        $this->connect($stmts);

        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new NodeConnector);
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        return $stmts;
    }

    private function connect($stmts)
    {
        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new NodeConnector);
        return $traverser->traverse($stmts);
    }

    private function range($range)
    {
        return LineRange::fromString($range);
    }

    private function statements($code)
    {
        if (strpos($code, '<?php') === false) {
            $code = "<?php\n" . $code;
        }

        $parser = new PHPParser_Parser(new PHPParser_Lexer());
        return $parser->parse($code);

    }
}
