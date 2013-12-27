<?php

namespace QafooLabs\Refactoring\Domain\Model;

class IndentationDetectorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMinIndentationForOneLine()
    {
        $detector = $this->createDetector(array('    echo "test";'));

        $this->assertEquals(4, $detector->getMinIndentation());
    }

    public function testGetMinIndentationForFirstLine()
    {
        $detector = $this->createDetector(array(
            '  echo "Line 1";',
            '    echo "Line 2";',
        ));

        $this->assertEquals(2, $detector->getMinIndentation());
    }

    public function testGetMinIntentationForLaterLine()
    {
        $detector = $this->createDetector(array(
            '    echo "Line 1";',
            '  echo "Line 2";',
        ));

        $this->assertEquals(2, $detector->getMinIndentation());
    }

    public function testGetMinIndentationWithBlankLines()
    {
        $detector = $this->createDetector(array(
            '',
            '    echo "test";',
        ));

        $this->assertEquals(4, $detector->getMinIndentation());
    }

    public function testGetFirstLineIndentation()
    {
        $detector = $this->createDetector(array(
            '    echo "line 1";',
            '  echo "line 2";',
        ));

        $this->assertEquals(4, $detector->getFirstLineIndentation());
    }

    public function testGetFirstLineIndentationWithBlankLines()
    {
        $detector = $this->createDetector(array(
            '',
            '  echo "test";',
        ));

        $this->assertEquals(2, $detector->getFirstLineIndentation());
    }

    private function createDetector(array $lines)
    {
        return new IndentationDetector(LineCollection::createFromArray($lines));
    }
}
