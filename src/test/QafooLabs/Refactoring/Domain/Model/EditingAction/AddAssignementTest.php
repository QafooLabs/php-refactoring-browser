<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\Variable;

class AddAssignmentTest extends \PHPUnit_Framework_TestCase
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
            new AddAssignment(4, new Variable('lhs'), 'rhs')
        );
    }

    public function testAssignementIsAppenedAtGivenLine()
    {
        $line = 27;

        $action = new AddAssignment($line, new Variable(''), '');

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($line, $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testPropertyCodeIsCorrect()
    {
        $action = new AddAssignment(5, new Variable('$this->value'), '$value');

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($this->anything(), $this->equalTo(array(
                '        $this->value = $value;',
                ''
              )));

        $action->performEdit($this->buffer);
    }
}
