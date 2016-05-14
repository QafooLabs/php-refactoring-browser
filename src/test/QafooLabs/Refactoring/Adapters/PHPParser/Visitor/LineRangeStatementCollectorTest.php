<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

use QafooLabs\Refactoring\Domain\Model\LineRange;

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
        $this->assertInstanceOf('PHPParser\Node\Expr\MethodCall', $collectedStatements[0]);
    }

    private function traverse($stmts, $visitor)
    {
        $this->connect($stmts);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NodeConnector);
        $traverser->addVisitor($visitor);
        $traverser->traverse($stmts);

        return $stmts;
    }

    private function connect($stmts)
    {
        $traverser = new NodeTraverser();
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

        $parserFactory = new ParserFactory();
        $parser = $parserFactory->create(ParserFactory::PREFER_PHP7);

        return $parser->parse($code);

    }
}
