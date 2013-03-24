<?php

namespace QafooLabs\Refactoring\Domain\Model;

class VariableTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateInvalidVariable()
    {
        $this->setExpectedException('QafooLabs\Refactoring\Domain\Model\RefactoringException', 'The given variable name "(); " is not valid in PHP.');

        new Variable('(); ');
    }

    public function testGetNameOrToken()
    {
        $variable = new Variable('$var');

        $this->assertEquals('var', $variable->getName());
        $this->assertEquals('$var', $variable->getToken());
    }

    public function testCreateInstanceVariable()
    {
        $variable = new Variable('$this->var');

        $this->assertEquals('this->var', $variable->getName());
        $this->assertEquals('$this->var', $variable->getToken());

        $this->assertTrue($variable->isInstance());
        $this->assertFalse($variable->isLocal());
    }

    public function testCreateLocalVariable()
    {
        $variable = new Variable('$var');

        $this->assertFalse($variable->isInstance());
        $this->assertTrue($variable->isLocal());
    }
}
