<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

class LocalVariableToInstanceTest extends \PHPUnit_Framework_TestCase
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
            new LocalVariableToInstance(
                new DefinedVariables(array(), array()),
                new Variable('testVar')
            )
        );
    }

    public function testItReplacesVariableWithInstanceVariableVersion()
    {
        $variable = new Variable('varName');

        $action = new LocalVariableToInstance(
            new DefinedVariables(array('varName' => array(1)), array()),
            $variable
        );

        $this->buffer
             ->expects($this->once())
             ->method('replaceString')
             ->with($this->anything(), $this->equalTo('$varName'), $this->equalTo('$this->varName'));

        $action->performEdit($this->buffer);
    }

    public function testItReplacesOnLineForReadOnlyVariable()
    {
        $definedVars = new DefinedVariables(array('theVar' => array(12)), array());
        $variable = new Variable('theVar');

        $action = new LocalVariableToInstance($definedVars, $variable);

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

        $action = new LocalVariableToInstance($definedVars, $variable);

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

        $action = new LocalVariableToInstance($definedVars, $variable);

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

        $action = new LocalVariableToInstance($definedVars, $variable);

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
