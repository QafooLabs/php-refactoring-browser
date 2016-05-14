<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\Variable;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

class RenameLocalVariableTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->scanner = \Phake::mock('QafooLabs\Refactoring\Domain\Services\VariableScanner');
        $this->codeAnalysis = \Phake::mock('QafooLabs\Refactoring\Domain\Services\CodeAnalysis');
        $this->editor = \Phake::mock('QafooLabs\Refactoring\Domain\Services\Editor');
        $this->refactoring = new RenameLocalVariable($this->scanner, $this->codeAnalysis, $this->editor);

        \Phake::when($this->codeAnalysis)->isLocalScope(\Phake::anyParameters())->thenReturn(true);
    }

    public function testRenameLocalVariable()
    {
        $buffer = \Phake::mock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');

        \Phake::when($this->scanner)->scanForVariables(\Phake::anyParameters())->thenReturn(
            new DefinedVariables(array('helloWorld' => array(6)))
        );
        \Phake::when($this->editor)->openBuffer(\Phake::anyParameters())->thenReturn($buffer);
        \Phake::when($this->codeAnalysis)->findMethodRange(\Phake::anyParameters())->thenReturn(LineRange::fromSingleLine(1));

        $patch = $this->refactoring->refactor(new File('foo.php', <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        $helloWorld = 'bar';
    }
}
PHP
            ), 6, new Variable('$helloWorld'), new Variable('$var'));

        \Phake::verify($buffer)->replaceString(6, '$helloWorld', '$var');
    }

    public function testRenameNonLocalVariable_ThrowsException()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'Given variable "$this->foo" is required to be local to the current method.');

        $this->refactoring->refactor(
            new File('foo.php', ''), 6,
            new Variable('$this->foo'),
            new Variable('$foo')
        );
    }

    public function testRenameIntoNonLocalVariable_ThrowsException()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'Given variable "$this->foo" is required to be local to the current method.');

        $this->refactoring->refactor(
            new File('foo.php', ''), 6,
            new Variable('$foo'),
            new Variable('$this->foo')
        );
    }
}
