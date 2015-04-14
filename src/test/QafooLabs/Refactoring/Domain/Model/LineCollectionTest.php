<?php

namespace QafooLabs\Refactoring\Domain\Model;

use QafooLabs\Refactoring\Utils\ToStringIterator;

class LineCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testItStoresLines()
    {
        $lineObjects = array(
            new Line('line 1'),
            new Line('line 2')
        );

        $lines = new LineCollection($lineObjects);

        $this->assertSame($lineObjects, $lines->getLines());
    }

    public function testAppendAddsALine()
    {
        $line1 = new Line('line 1');
        $line2 = new Line('line 2');

        $lines = new LineCollection(array($line1));

        $lines->append($line2);

        $this->assertSame(array($line1, $line2), $lines->getLines());
    }

    public function testAppendStringAddsALine()
    {
        $line1 = 'line 1';
        $line2 = 'line 2';

        $lines = new LineCollection(array(new Line($line1)));

        $lines->appendString($line2);

        $this->assertEquals(
            array(new Line($line1), new Line($line2)),
            $lines->getLines()
        );
    }

    public function testCreateFromArray()
    {
        $lines = LineCollection::createFromArray(array(
            'line1',
            'line2',
        ));

        $this->assertEquals(
            array(new Line('line1'), new Line('line2')),
            $lines->getLines()
        );
    }

    public function testCreateFromString()
    {
        $lines = LineCollection::createFromString(
            "line1\nline2"
        );

        $this->assertEquals(
            array(new Line('line1'), new Line('line2')),
            $lines->getLines()
        );
    }

    public function testIsIterable()
    {
        $lineObjects = array(
            new Line('line 1'),
            new Line('line 2')
        );

        $lines = new LineCollection($lineObjects);

        $this->assertEquals($lineObjects, iterator_to_array($lines));
    }

    public function testAppendLinesAddsGivenLines()
    {
        $lines = LineCollection::createFromArray(array(
            'line1',
            'line2',
        ));

        $lines->appendLines(LineCollection::createFromArray(array(
            'line3',
            'line4',
        )));

        $this->assertEquals(
            array('line1', 'line2', 'line3', 'line4'),
            iterator_to_array(new ToStringIterator($lines->getIterator()))
        );
    }

    public function testAppendlankLine()
    {
        $lines = new LineCollection();

        $lines->appendBlankLine();

        $this->assertEquals(
            array(''),
            iterator_to_array(new ToStringIterator($lines->getIterator()))
        );
    }
}
