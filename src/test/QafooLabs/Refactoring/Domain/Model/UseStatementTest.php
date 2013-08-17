<?php

namespace QafooLabs\Refactoring\Domain\Model;

class UseStatementTest extends \PHPUnit_Framework_TestCase
{
    private $useStatement;

    public function setUp()
    {
        parent::setUp();

        $file = File::createFromPath(__FILE__, __DIR__);
        $this->useStatement = new UseStatement($file, LineRange::fromLines(3,5));
    }

    public function testReturnsEndLineFromLineRange()
    {
        $this->assertEquals(5, $this->useStatement->getEndLine());
    }
}
