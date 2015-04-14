<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;

class ReplaceWithMethodCallTest extends \PHPUnit_Framework_TestCase
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
            new ReplaceWithMethodCall(
                LineRange::fromLines(1, 2),
                new MethodSignature('testMethod')
            )
        );
    }

    public function testBufferReplacesAtGivenRange()
    {
        $range = LineRange::fromLines(1, 2);

        $action = new ReplaceWithMethodCall(
            $range,
            new MethodSignature('testMethod')
        );

        $this->buffer
             ->expects($this->once())
             ->method('replace')
             ->with($this->equalTo($range), $this->anything());

        $this->setCodeBeingReplaced();

        $action->performEdit($this->buffer);
    }

    public function testMethodCallIsCorrectForSimpleMethod()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod')
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('$this->testMethod();', $action);
    }

    public function testMethodCallUsesGivenMethodName()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('realMethod')
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('$this->realMethod();', $action);
    }

    public function testStaticMethodCall()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', MethodSignature::IS_STATIC)
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('self::testMethod();', $action);
    }

    public function testMethodCallWithSingleReturnVariable()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, array(), array('result'))
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches('$result = $this->testMethod();', $action);
    }

    public function testMethodCallWithMultipleReturnVariables()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, array(), array('result1', 'result2'))
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches(
            'list($result1, $result2) = $this->testMethod();',
            $action
        );
    }

    public function testMethodCallWithArguments()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, array('arg1', 'arg2'))
        );

        $this->setCodeBeingReplaced();

        $this->assertGeneratedMethodCallMatches(
            '$this->testMethod($arg1, $arg2);',
            $action
        );
    }

    public function testExtractedRangeIsReadFromTheBuffer()
    {
        $range = LineRange::fromLines(1, 2);

        $this->buffer
             ->expects($this->once())
             ->method('getLines')
             ->with($this->equalTo($range))
             ->will($this->returnValue(array()));

        $action = new ReplaceWithMethodCall(
            $range,
            new MethodSignature('testMethod')
        );

        $action->performEdit($this->buffer);
    }

    public function testExtractRangeIndentsMethodCallForFirstLineWithExtraIndent()
    {
        $lines = array(
            '            echo "Something";',
        );

        $this->buffer
             ->expects($this->once())
             ->method('getLines')
             ->will($this->returnValue($lines));

        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod')
        );

        $this->assertGeneratedMethodCallMatches(
            '$this->testMethod();',
            $action,
            12
        );
    }

    private function setCodeBeingReplaced(
        array $lines = array('        echo "Replace me";')
    ) {
        $this->buffer
             ->expects($this->any())
             ->method('getLines')
             ->will($this->returnValue($lines));
    }

    private function assertGeneratedMethodCallMatches($expected, $action, $indentSize = 8)
    {
        $expected = str_repeat(' ', $indentSize) . $expected;

        $this->buffer
             ->expects($this->once())
             ->method('replace')
             ->with($this->anything(), $this->equalTo(array($expected)));

        $action->performEdit($this->buffer);
    }
}
