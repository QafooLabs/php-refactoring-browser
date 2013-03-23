<?php

namespace QafooLabs\Refactoring\Application\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LineRangeStatementCollector;
use QafooLabs\Refactoring\Adapters\PHPParser\Visitor\LocalVariableClassifier;
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

    private function scanForVariables($code, $range)
    {
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

        $localVariableClassifier = new LocalVariableClassifier();
        $traverser     = new PHPParser_NodeTraverser;
        $traverser->addVisitor($localVariableClassifier);
        $traverser->traverse($selectedStatements);

        $localVariables = $localVariableClassifier->getUsedLocalVariables();
        $assignments = $localVariableClassifier->getAssignments();

        return array($localVariables, $assignments, $selectedStatements, $stmts);
    }

    private function generateMethodCall($newMethodName, $localVariables, $assignments)
    {
        $arguments = array();

        foreach ($localVariables as $localVariable) {
            $arguments[] = new \PHPParser_Node_Arg(
                new \PHPParser_Node_Expr_Variable($localVariable),
                false
            );
        }

        $methodCall = new \PHPParser_Node_Expr_MethodCall(
            new \PHPParser_Node_Expr_Variable("this"),
            $newMethodName,
            $arguments
        );

        if (count($assignments) == 1) {
            $methodCall = new \PHPParser_Node_Expr_Assign(
                new \PHPParser_Node_Expr_Variable($assignments[0]),
                $methodCall
            );
        }

        return $methodCall;
    }

    private function replaceStatementsWithMethodCall($selectedStatements, $methodCall)
    {
        $traverser = new PHPParser_NodeTraverser;
        $traverser->addVisitor(new StatementReplacer($selectedStatements, $methodCall));

        // TODO: Only works for simple case
        $methodNode = $selectedStatements[0]->getAttribute('parent');

        $traverser->traverse($methodNode->stmts);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $range = LineRange::fromString($input->getArgument('range'));
        $newMethodName = $input->getArgument('newmethod');

        $code = file_get_contents($file);

        list ($localVariables, $assignments, $selectedStatements, $stmts) = $this->scanForVariables($code, $range);

        $methodCall = $this->generateMethodCall($newMethodName, $localVariables, $assignments);
        $this->replaceStatementsWithMethodCall($selectedStatements, $methodCall);

        $this->appendNewMethod($newMethodName, $selectedStatements, $localVariables, $assignments);

        $output->writeln($this->generateDiff($code, $stmts));
    }

    private function generateDiff($code, $stmts)
    {
        $prettyPrinter = new \PHPParser_PrettyPrinter_Zend;
        $newCode = "<?php\n" . $prettyPrinter->prettyPrint($stmts);

        $diff = \Scrutinizer\Util\DiffUtils::generate($code, $newCode);

        return $diff;
    }

    private function appendNewMethod($newMethodName, $selectedStatements, $localVariables, $assignments)
    {
        if (count($assignments) == 1) {
            $selectedStatements[] = new \PHPParser_Node_Stmt_Return(
                new \PHPParser_Node_Expr_Variable($assignments[0])
            );
        }

        $params = array();
        $methodNode = $selectedStatements[0]->getAttribute('parent');
        $classNode = $methodNode->getAttribute('parent');

        $classStmts = $classNode->stmts;

        $type = \PHPParser_Node_Stmt_Class::MODIFIER_PRIVATE;
        if ($methodNode->type & \PHPParser_Node_Stmt_Class::MODIFIER_STATIC) {
            $type |= \PHPParser_Node_Stmt_Class::MODIFIER_STATIC;
        }

        foreach ($localVariables as $localVariable) {
            $params[] = new \PHPParser_Node_Param(
                $localVariable,
                null,
                null,
                false
            );
        }

        $classStmts[] = new \PHPParser_Node_Stmt_ClassMethod($newMethodName, array(
            'type'   => $type,
            'stmts'  => $selectedStatements,
            'params' => $params,
        ));

        $classNode->stmts = $classStmts;
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
