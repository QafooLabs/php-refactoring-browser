<?php

namespace QafooLabs\Refactoring\Application\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Stmt;
use PHPParser_NodeTraverser;

/**
 * Symfony Adapter to execute the Extract Method Refactoring
 */
class ExtractMethodCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('extract-method')
            ->setDescription('Extract a list of statements into a method.')
            ->addArgument('file', InputArgument::REQUIRED, 'File that contains list of statements to extract')
            ->addArgument('range', InputArgument::REQUIRED, 'Line Range of statements that should be extracted.')
            ->addArgument('newmethod', InputArgument::REQUIRED, 'Name of the new method.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $range = LineRange::fromString($input->getArgument('range'));
        $newMethodName = $input->getArgument('newmethod');

        $code = file_get_contents($file);

        $parser = new PHPParser_Parser();
        $stmts = $parser->parse(new PHPParser_Lexer($code));

        $methodCall = new \PHPParser_Node_Expr_MethodCall(
            new \PHPParser_Node_Expr_Variable("this"),
            $newMethodName,
            array()
        );

        $collector = new LineRangeStatementCollector($range);

        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new \PHPParser_NodeVisitor_NodeConnector);
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if ( ! $selectedStatements) {
            return;
        }

        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new StatementReplacer($selectedStatements, $methodCall));

        // TODO: Only works for simple case
        $methodNode = $selectedStatements[0]->getAttribute('parent');
        $classNode = $methodNode->getAttribute('parent');

        $traverser->traverse($methodNode->stmts);

        $type = \PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE;
        if ($methodNode->type & \PHPParser_Node_Stmt_Class::MODIFIER_STATIC) {
            $type |= \PHPParser_Node_Stmt_Class::MODIFIER_STATIC;
        }

        $classStmts = $classNode->stmts;
        $classStmts[] = new \PHPParser_Node_Stmt_ClassMethod($newMethodName, array(
            'type' => $type,
            'stmts' => $selectedStatements
        ));

        $classNode->stmts = $classStmts;

        $prettyPrinter = new \PHPParser_PrettyPrinter_Zend;
        $newCode = "<?php\n" . $prettyPrinter->prettyPrint($stmts);

        $diff = \Scrutinizer\Util\DiffUtils::generate($code, $newCode);
        $output->writeln($diff);
    }
}

class LineRange
{
    private $lines = array();

    static public function fromString($range)
    {
        list($start, $end) = explode("-", $range);

        $list = new self();

        for ($i = $start; $i <= $end; $i++) {
            $list->lines[$i] = $i;
        }

        return $list;
    }

    public function isInRange($line)
    {
        return isset($this->lines[$line]);
    }
}

class LineRangeStatementCollector extends \PHPParser_NodeVisitorAbstract
{
    private $lineRange;
    private $statements;

    public function __construct(LineRange $lineRange)
    {
        $this->lineRange = $lineRange;
    }

    public function enterNode(PHPParser_Node $node)
    {
        if ( ! $this->lineRange->isInRange($node->getLine())) {
            return;
        }

        if ( ! ($node instanceof PHPParser_Node_Stmt)) {
            return;
        }

        $this->statements[] = $node;
    }

    public function getStatements()
    {
        return $this->statements;
    }
}

class StatementReplacer extends \PHPParser_NodeVisitorAbstract
{
    private $statements;
    private $replace;

    public function __construct(array $statements, $replace)
    {
        $this->statements = $statements;
        $this->replace = $replace;
    }

    public function enterNode(PHPParser_Node $node)
    {
        if (array_search($node, $this->statements, true) === false) {
            return;
        }

        $parent = $node->getAttribute('parent');

        $newChildren = array();
        foreach ($parent->stmts as $child) {
            if ($child === $node) {
                if ($this->replace !== null) {
                    $newChildren[] = $this->replace;
                    $this->replace = null;
                }
                continue;
            }

            $newChildren[] = $child;
        }

        $parent->stmts = $newChildren;
    }
}
