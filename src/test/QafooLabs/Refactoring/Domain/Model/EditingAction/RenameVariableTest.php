<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

class RenameVariableTest extends \PHPUnit_Framework_TestCase
{
    private $buffer;

    protected function setUp()
    {
        $this->buffer = $this->getMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');
    }

    public function testItIsAnEditingAction()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\EditingAction',
            new RenameVariable(
                new DefinedVariables(array(), array()),
                new Variable('testVar'),
                new Variable('newVar')
            )
        );
    }

    public function testItReplacesVariableWithInstanceVariableVersion()
    {
        $oldName = new Variable('varName');
        $newName = new Variable('newName');

        $action = new RenameVariable(
            new DefinedVariables(array('varName' => array(1)), array()),
            $oldName,
            $newName
        );

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->anything(), $this->equalTo('$varName'), $this->equalTo('$newName'));

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOnLineForReadOnlyVariable()
    {
        $definedVars = new DefinedVariables(array('theVar' => array(12)), array());
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->equalTo(12), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOn2LinesForReadOnlyVariable()
    {
        $definedVars = new DefinedVariables(array('theVar' => array(12, 15)), array());
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
             ->expects($this->at(0))
             ->method('replaceString')
             ->with($this->equalTo(12), $this->anything(), $this->anything());

        $this->buffer
             ->expects($this->at(1))
             ->method('replaceString')
             ->with($this->equalTo(15), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOnLineForChangedVariable()
    {
        $definedVars = new DefinedVariables(array(), array('theVar' => array(12)));
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->equalTo(12), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOn2LinesForChangedVariable()
    {
        $definedVars = new DefinedVariables(array(), array('theVar' => array(12, 15)));
        $variable = new Variable('theVar');

        $action = new RenameVariable($definedVars, $variable, $variable);

        $this->buffer
             ->expects($this->at(0))
             ->method('replaceString')
             ->with($this->equalTo(12), $this->anything(), $this->anything());

        $this->buffer
             ->expects($this->at(1))
             ->method('replaceString')
             ->with($this->equalTo(15), $this->anything(), $this->anything());

        $action->performEdit($this->buffer);
    }
}
