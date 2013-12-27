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

        $action->performEdit($this->buffer);
    }

    public function testMethodCallIsCorrectForSimpleMethod()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod')
        );

        $this->assertGeneratedMethodCallMatches('$this->testMethod();', $action);
    }

    public function testMethodCallUsesGivenMethodName()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('realMethod')
        );

        $this->assertGeneratedMethodCallMatches('$this->realMethod();', $action);
    }

    public function testStaticMethodCall()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', MethodSignature::IS_STATIC)
        );

        $this->assertGeneratedMethodCallMatches('self::testMethod();', $action);
    }

    public function testMethodCallWithSingleReturnVariable()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, array(), array('result'))
        );

        $this->assertGeneratedMethodCallMatches('$result = $this->testMethod();', $action);
    }

    public function testMethodCallWithMultipleReturnVariables()
    {
        $action = new ReplaceWithMethodCall(
            LineRange::fromLines(1, 2),
            new MethodSignature('testMethod', 0, array(), array('result1', 'result2'))
        );

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

        $this->assertGeneratedMethodCallMatches(
            '$this->testMethod($arg1, $arg2);',
            $action
        );
    }

    private function assertGeneratedMethodCallMatches($expected, $action)
    {
        $expected = '        ' . $expected;

        $this->buffer
             ->expects($this->once())
             ->method('replace')
             ->with($this->anything(), $this->equalTo(array($expected)));

        $action->performEdit($this->buffer);
    }
}
