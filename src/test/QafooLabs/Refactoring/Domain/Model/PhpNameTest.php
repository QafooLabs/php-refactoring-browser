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

namespace QafooLabs\Refactoring\Domain\Model;

class PhpNameTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAffectedByChangesToItself()
    {
        $name = new PhpName("Foo\Bar\Baz", "Baz", null, null);

        $this->assertTrue($name->isAffectedByChangesTo($name));
    }

    public function testIsNotAffectedByChangesToNonRelativePart()
    {
        $name = new PhpName("Foo\Bar\Baz", "Baz", null, null);
        $changing = new PhpName("Foo\Bar", "Foo\Bar", null, null);

        $this->assertFalse($name->isAffectedByChangesTo($changing));
    }

    public function testIsAffectedByRelativeChanges()
    {
        $name = new PhpName("Foo\Bar\Baz", "Bar\Baz", null, null);
        $changing = new PhpName("Foo\Bar", "Foo\Bar", null, null);

        $this->assertTrue($name->isAffectedByChangesTo($changing));
    }

    public function testRelativeChanges()
    {
        $name = new PhpName("Foo\Bar\Baz", "Bar\Baz", null, null);
        $from = new PhpName("Foo\Bar", "Foo\Bar", null, null);
        $to = new PhpName("Foo\Baz", "Foo\Baz", null, null);

        $newName = $name->change($from, $to);

        $this->assertEquals('Foo\Baz\Baz', $newName->fullyQualifiedName());
        $this->assertEquals('Baz\Baz', $newName->relativeName());
    }

    public function testRegression()
    {
        $name = new PhpName("Bar\Bar", "Bar\Bar", null, null);
        $changing = new PhpName("Bar", "Bar", null, null);

        $this->assertTrue($name->isAffectedByChangesTo($changing));
    }

    public function testRegression2()
    {
        $name = new PhpName("Foo\\Foo", "Foo\\Foo", null, null);
        $from = new PhpName("Foo\\Foo", "Foo", null, null);
        $to = new PhpName("Foo\\Bar", "Bar", null, null);

        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\\Bar', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\\Bar', $changed->relativeName());
    }
}
