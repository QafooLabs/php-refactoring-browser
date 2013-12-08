<?php
/**
 * qafoo php refactoring browser
 *
 * license
 *
 * this source file is subject to the mit license that is bundled
 * with this package in the file license.txt.
 * if you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so i can send you a copy immediately.
 */

namespace QafooLabsTests\Refactoring\Adapters\Patches;

use QafooLabs\Refactoring\Adapters\Patches\PatchBuffer;
use QafooLabs\Refactoring\Domain\Model\LineRange;

/**
 * Unit tests for {@see PatchBuffer}.
 *
 * @covers QafooLabs\Refactoring\Adapters\Patches\PatchBuffer
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class PatchBufferTest extends \PHPUnit_Framework_TestCase
{
    private $buffer;

    private $builder;

    /**
     * Set up the instance to be tested.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->builder = $this->getMockBuilder('QafooLabs\Patches\PatchBuilder')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->buffer = new PatchBuffer($this->builder);
    }

    public function testGetLinesFetchesLinesFromTheBuilder()
    {
        $startLine = 5;
        $endLine   = 7;

        $lines = array('aaa','bbb');

        $this->builder
             ->expects($this->once())
             ->method('getLines')
             ->with($this->equalTo($startLine), $this->equalTo($endLine))
             ->will($this->returnValue($lines));

        $this->assertEquals(
            $lines,
            $this->buffer->getLines(LineRange::fromLines($startLine, $endLine))
        );
    }
}
