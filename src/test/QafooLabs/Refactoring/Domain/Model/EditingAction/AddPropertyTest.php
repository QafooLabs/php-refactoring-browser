<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

class AddPropertyTest extends \PHPUnit_Framework_TestCase
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
            new AddProperty(5, 'testProperty')
        );
    }

    public function testPropertyIsAppenedAtGivenLine()
    {
        $line = 27;

        $action = new AddProperty($line, '');

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($line, $this->anything());

        $action->performEdit($this->buffer);
    }

    public function testPropertyCodeIsCorrect()
    {
        $action = new AddProperty(5, 'testProperty');

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($this->anything(), $this->equalTo(array(
                '    private $testProperty;',
                ''
              )));

        $action->performEdit($this->buffer);
    }
}
