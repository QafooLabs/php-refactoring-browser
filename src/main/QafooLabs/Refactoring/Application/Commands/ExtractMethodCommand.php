<?php

namespace QafooLabs\Refactoring\Application\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Domain\Model\LineRange;

use PHPParser_Parser;
use PHPParser_Lexer;
use PHPParser_Node;
use PHPParser_Node_Stmt;
use PHPParser_Node_Expr_FuncCall;
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

        $collector = new LineRangeStatementCollector($range);

        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new \PHPParser_NodeVisitor_NodeConnector);
        $traverser->addVisitor($collector);

        $traverser->traverse($stmts);

        $selectedStatements = $collector->getStatements();

        if ( ! $selectedStatements) {
            throw new \RuntimeException("No statements found in line range.");
        }

        $localVariableCollector = new LocalVariableCollector();
        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor($localVariableCollector);
        $traverser->traverse($selectedStatements);

        $localVariables = $localVariableCollector->getLocalVariables();

        $arguments = array();
        $params = array();
        foreach ($localVariables as $localVariable) {
            $arguments[] = new \PHPParser_Node_Arg(
                new \PHPParser_Node_Expr_Variable($localVariable->name),
                false
            );
            $params[] = new \PHPParser_Node_Param(
                $localVariable->name,
                null,
                null,
                false
            );
        }

        $methodCall = new \PHPParser_Node_Expr_MethodCall(
            new \PHPParser_Node_Expr_Variable("this"),
            $newMethodName,
            $arguments
        );

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
            'type'   => $type,
            'stmts'  => $selectedStatements,
            'params' => $params,
        ));

        $classNode->stmts = $classStmts;

        $prettyPrinter = new \PHPParser_PrettyPrinter_Zend;
        $newCode = "<?php\n" . $prettyPrinter->prettyPrint($stmts);

        $diff = \Scrutinizer\Util\DiffUtils::generate($code, $newCode);
        $output->writeln($diff);
    }
}

class LocalVariableCollector extends \PHPParser_NodeVisitorAbstract
{
    private $localVariables = array();

    public function enterNode(PHPParser_Node $node)
    {
        if ( ! ($node instanceof \PHPParser_Node_Expr_Variable)) {
            return;
        }

        if ($node->name === "this") {
            return;
        }

        $this->localVariables[] = $node;
    }

    public function getLocalVariables()
    {
        return $this->localVariables;
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
