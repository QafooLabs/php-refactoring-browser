<?php

namespace QafooLabs\Refactoring\Domain\Model\EditingAction;

use QafooLabs\Refactoring\Domain\Model\MethodSignature;

use QafooLabs\Refactoring\Domain\Model\LineCollection;

class AddMethodTest extends \PHPUnit_Framework_TestCase
{
    private $action;

    private $buffer;

    protected function setUp()
    {
        $this->buffer = $this->getMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');
    }

    public function testItIsAnEditingAction()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\EditingAction',
            new AddMethod(0, new MethodSignature('test'), new LineCollection())
        );
    }

    public function testBufferAppendIsPerformedAtTheGivenLineNumber()
    {
        $lineNumber = 27;

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($this->equalTo($lineNumber), $this->anything());

        $action = new AddMethod($lineNumber, new MethodSignature('test'), new LineCollection());

        $action->performEdit($this->buffer);
    }

    public function testAppendsMethod()
    {
        $action = new AddMethod(0, new MethodSignature('testMethod'), new LineCollection());

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod()',
            '    {',
            '    }'
        ), $action);
    }

    public function testReturnStatementForSingleVariable()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(array('returnVar')),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod()',
            '    {',
            '',
            '        return $returnVar;',
            '    }'
        ), $action);
    }

    public function testReturnStatementForSingleVariableHasCorrectName()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(array('specialVar')),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod()',
            '    {',
            '',
            '        return $specialVar;',
            '    }'
        ), $action);
    }

    public function testReturnStatementForMultipleVariables()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(array('ret1', 'ret2')),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod()',
            '    {',
            '',
            '        return array($ret1, $ret2);',
            '    }'
        ), $action);
    }

    public function testMethodNameIsUsed()
    {
        $action = new AddMethod(
            0,
            new MethodSignature('realMethodName'),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function realMethodName()',
            '    {',
            '    }'
        ), $action);
    }

    public function testStaticMethodsAreDefinedCorrectly()
    {
        $action = new AddMethod(
            0,
            new MethodSignature(
                'realMethodName',
                MethodSignature::IS_PRIVATE | MethodSignature::IS_STATIC
            ),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private static function realMethodName()',
            '    {',
            '    }'
        ), $action);
    }

    public function testMethodArgumentsAreDefinedCorrectly()
    {
        $action = new AddMethod(
            0,
            new MethodSignature(
                'testMethod',
                MethodSignature::IS_PRIVATE,
                array('param1', 'param2')
            ),
            new LineCollection()
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod($param1, $param2)',
            '    {',
            '    }'
        ), $action);
    }

    public function testSelectedCodeIsAdded()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(array()),
            LineCollection::createFromArray(array(
                'echo "Hello World!";'
            ))
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod()',
            '    {',
            '        echo "Hello World!";',
            '    }'
        ), $action);
    }

    public function testSelectedCodeIsAddedWithCorrectIndetations()
    {
        $action = new AddMethod(
            0,
            $this->createMethodSignatureWithReturnVars(array()),
            LineCollection::createFromArray(array(
                '    if ($something) {',
                '        echo "Hello World!";',
                '    }'
            ))
        );

        $this->assertGeneratedCodeMatches(array(
            '',
            '    private function testMethod()',
            '    {',
            '        if ($something) {',
            '            echo "Hello World!";',
            '        }',
            '    }'
        ), $action);
    }

    private function assertGeneratedCodeMatches(array $expected, AddMethod $action)
    {
        $this->makeBufferAppendExpectCode($expected);

        $action->performEdit($this->buffer);
    }

    private function createMethodSignatureWithReturnVars(array $returnVars)
    {
        return new MethodSignature(
            'testMethod',
            MethodSignature::IS_PRIVATE,
            array(),
            $returnVars
        );
    }

    private function makeBufferAppendExpectCode(array $codeLines)
    {
        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($this->anything(), $this->equalTo($codeLines));
    }
}
