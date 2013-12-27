<?php

namespace QafooLabs\Refactoring\Domain\Model;

use QafooLabs\Refactoring\Utils\ToStringIterator;

class IndentingLineCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $lines;

    protected function setUp()
    {
        $this->lines = new IndentingLineCollection();
    }

    public function testIsALineCollection()
    {
        $this->assertInstanceOf(
            'QafooLabs\Refactoring\Domain\Model\LineCollection',
            $this->lines
        );
    }

    public function testAppendAddsIndentation()
    {
        $this->lines->addIndentation();

        $this->lines->append(new Line('echo "test";'));

        $this->assertLinesMatch(array(
            '    echo "test";'
        ));
    }

    public function testAppendAddsMulitpleIndentation()
    {
        $this->lines->append(new Line('echo "line1";'));
        $this->lines->addIndentation();
        $this->lines->append(new Line('echo "line2";'));
        $this->lines->addIndentation();
        $this->lines->append(new Line('echo "line3";'));

        $this->assertLinesMatch(array(
            'echo "line1";',
            '    echo "line2";',
            '        echo "line3";'
        ));
    }

    public function testAppendRemovesIndentation()
    {
        $this->lines->append(new Line('echo "line1";'));
        $this->lines->addIndentation();
        $this->lines->append(new Line('echo "line2";'));
        $this->lines->removeIndentation();
        $this->lines->append(new Line('echo "line3";'));

        $this->assertLinesMatch(array(
            'echo "line1";',
            '    echo "line2";',
            'echo "line3";'
        ));
    }

    public function testAppendStringObeysIndentation()
    {
        $this->lines->appendString('echo "line1";');
        $this->lines->addIndentation();
        $this->lines->appendString('echo "line2";');
        $this->lines->removeIndentation();
        $this->lines->appendString('echo "line3";');

        $this->assertLinesMatch(array(
            'echo "line1";',
            '    echo "line2";',
            'echo "line3";'
        ));
    }

    public function testAppendLinesObeysIndentation()
    {
        $this->lines->addIndentation();

        $this->lines->appendLines(LineCollection::createFromArray(array(
            'echo "line1";',
            'echo "line2";'
        )));

        $this->assertLinesMatch(array(
            '    echo "line1";',
            '    echo "line2";',
        ));
    }

    public function testAddBlankLineContainsNoIndentation()
    {
        $this->lines->appendBlankLine();

        $this->assertLinesMatch(array(''));
    }

    private function assertLinesMatch(array $expected)
    {
        $this->assertEquals(
            $expected,
            iterator_to_array(new ToStringIterator($this->lines->getIterator()))
        );
    }
}
