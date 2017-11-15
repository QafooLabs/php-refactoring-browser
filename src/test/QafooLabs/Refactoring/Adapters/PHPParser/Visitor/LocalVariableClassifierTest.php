<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;

class LocalVariableClassifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function givenVariable_WhenClassification_ThenLocalVariableFound()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Expr\Variable('foo');

        $classifier->enterNode($variable);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenAssignment_WhenClassification_ThenAssignmentFound()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new Expr\Assign(
            new Expr\Variable('foo'),
            new Expr\Variable('bar')
        );

        $classifier->enterNode($assign);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }

    /**
     * @test
     */
    public function givenAssignmentAndReadOfSameVariable_WhenClassification_ThenFindBoth()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new Expr\Assign(
            new Expr\Variable('foo'),
            new Expr\Variable('foo')
        );

        $traverser = new NodeTraverser();
        $traverser->addVisitor($classifier);
        $traverser->traverse(array($assign));

        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenThisVariable_WhenClassification_ThenNoLocalVariables()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new Expr\Variable('this');

        $classifier->enterNode($variable);

        $this->assertEquals(array(), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenParam_WhenClassification_FindAsAssignment()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new \PhpParser\Node\Param('foo');

        $classifier->enterNode($variable);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }

    /**
     * @test
     * @group GH-4
     */
    public function givenArrayDimFetchASsignment_WhenClassification_FindAsAssignmentAndRead()
    {
        $classifier = new LocalVariableClassifier();

        $assign = new Expr\Assign(
            new Expr\ArrayDimFetch(
                new Expr\Variable('foo')
            ),
            new Expr\Variable('bar')
        );

        $classifier->enterNode($assign);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }
}
