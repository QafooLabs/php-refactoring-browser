<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace QafooLabsTests\Refactoring\Domain\Model;

use QafooLabs\Refactoring\Domain\Model\EditingSession;
use QafooLabs\Refactoring\Domain\Model\MethodSignature;
use QafooLabs\Refactoring\Domain\Model\LineRange;

/**
 * Unit tests for {@see EditingSession}.
 *
 * @covers QafooLabs\Refactoring\Domain\Model\EditingSession
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class EditingSessionTest extends \PHPUnit_Framework_TestCase
{
    private $session;

    private $buffer;

    /**
     * Set up the instance to be tested.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->buffer = $this->getMock('QafooLabs\Refactoring\Domain\Model\EditorBuffer');

        $this->session = new EditingSession($this->buffer);
    }

    public function testAddMethodCanHandleDeeperLevelsOfIndentation()
    {
        // 3 levels of indentation
        $selectedCode = array("            echo 'Code to move';");

        $expected = array(
            '',
            '    private function extractedMethod()',
            '    {',
            '        echo \'Code to move\';',
            '    }'
        );

        $this->buffer
             ->expects($this->once())
             ->method('append')
             ->with($this->anything(), $this->equalTo($expected));

        $this->session->addMethod(1, new MethodSignature('extractedMethod'), $selectedCode);
    }

    public function testReplaceRangeWithMethodCallFetchesLinesFromBuffer()
    {
        $range = LineRange::fromLines(4, 10);

        $this->buffer
             ->expects($this->once())
             ->method('getLines')
             ->with($this->equalTo($range))
             ->will($this->returnValue(array('')));

        $this->session->replaceRangeWithMethodCall($range, new MethodSignature('methodName'));
    }

    public function testReplaceRangeWithMethodCallTakesIndentFromFirstLineOfCodeBeingReplaced()
    {
        $lines = array(
            '            echo \'Old Code\';',            // 3 levels of indentation
            '    echo \'Reverse indent\';',              // 1 levels of indentation
            '                echo \'Further indent\';',  // 4 levels of indentation
        );

        $methodSignature = new MethodSignature('newMethod');

        // 3 levels of indentation
        $expectedMethodCall = array('            $this->newMethod();');

        $this->buffer
             ->expects($this->once())
             ->method('getLines')
             ->will($this->returnValue($lines));

        $this->buffer
             ->expects($this->once())
             ->method('replace')
             ->with($this->anything(), $this->equalTo($expectedMethodCall));

        $this->session->replaceRangeWithMethodCall(LineRange::fromLines(1,2), $methodSignature);
    }
}
