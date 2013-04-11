<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser\Visitor;

class LocalVariableClassifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function givenVariable_WhenClassification_ThenLocalVariableFound()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new \PHPParser_Node_Expr_Variable("foo");

        $classifier->enterNode($variable);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenAssignment_WhenClassification_ThenAssignmentFound()
    {
        $classifier = new LocalVariableClassifier();
        $assign = new \PHPParser_Node_Expr_Assign(
            new \PHPParser_Node_Expr_Variable("foo"),
            new \PHPParser_Node_Expr_Variable("bar")
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
        $assign = new \PHPParser_Node_Expr_Assign(
            new \PHPParser_Node_Expr_Variable("foo"),
            new \PHPParser_Node_Expr_Variable("foo")
        );

        $traverser     = new \PHPParser_NodeTraverser;
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
        $variable = new \PHPParser_Node_Expr_Variable("this");

        $classifier->enterNode($variable);

        $this->assertEquals(array(), $classifier->getLocalVariables());
    }

    /**
     * @test
     */
    public function givenParam_WhenClassification_FindAsAssignment()
    {
        $classifier = new LocalVariableClassifier();
        $variable = new \PHPParser_Node_Param("foo");

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

        $assign = new \PHPParser_Node_Expr_Assign(
            new \PHPParser_Node_Expr_ArrayDimFetch(
                new \PHPParser_Node_Expr_Variable("foo")
            ),
            new \PHPParser_Node_Expr_Variable("bar")
        );

        $classifier->enterNode($assign);

        $this->assertEquals(array('foo' => array(-1)), $classifier->getLocalVariables());
        $this->assertEquals(array('foo' => array(-1)), $classifier->getAssignments());
    }
}
