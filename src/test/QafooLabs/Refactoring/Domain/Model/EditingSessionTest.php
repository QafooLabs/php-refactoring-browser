<?php

namespace QafooLabs\Refactoring\Domain\Model;

class EditingSessionTest extends \PHPUnit_Framework_TestCase
{
    private $session;

    private $buffer;

    protected function setUp()
    {
        $this->buffer = $this->getMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');

        $this->session = new EditingSession($this->buffer);
    }

    public function testEditActionsArePerformed()
    {
        $action1 = $this->getMock('QafooLabs\Refactoring\Domain\Model\EditingAction');
        $action2 = $this->getMock('QafooLabs\Refactoring\Domain\Model\EditingAction');

        $action1->expects($this->once())
                ->method('performEdit')
                ->with($this->equalTo($this->buffer));

        $action2->expects($this->once())
                ->method('performEdit')
                ->with($this->equalTo($this->buffer));

        $this->session->addEdit($action1);
        $this->session->addEdit($action2);

        $this->session->performEdits();
    }
}
