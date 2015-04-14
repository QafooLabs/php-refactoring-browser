<?php

namespace QafooLabs\Refactoring\Domain\Model;

class LineTest extends \PHPUnit_Framework_TestCase
{
    public function testItStoresTheLineOfCode()
    {
        $content = 'echo "Hello world!";';

        $line = new Line($content);

        $this->assertEquals($content, (string) $line);
    }

    public function testIsEmptyForEmptyLine()
    {
        $line = new Line('');

        $this->assertTrue($line->isEmpty());
    }

    public function testIsEmptyForLineWithContent()
    {
        $line = new Line('$a = 5;');

        $this->assertFalse($line->isEmpty());
    }

    public function testGetIndentationFor2Spaces()
    {
        $line = new Line('  echo "Test";');

        $this->assertEquals(2, $line->getIndentation());
    }

    public function testGetIndentationFor4Spaces()
    {
        $line = new Line('    echo "Test";');

        $this->assertEquals(4, $line->getIndentation());
    }
}
