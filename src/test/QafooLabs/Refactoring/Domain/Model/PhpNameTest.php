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
        $name = new PhpName("Foo\Bar\Baz", "Baz");

        $this->assertTrue($name->isAffectedByChangesTo($name));
    }

    public function testIsNotAffectedByChangesToNonRelativePart()
    {
        $name = new PhpName("Foo\Bar\Baz", "Baz");
        $changing = new PhpName("Foo\Bar", "Foo\Bar");

        $this->assertFalse($name->isAffectedByChangesTo($changing));
    }

    public function testIsAffectedByRelativeChanges()
    {
        $name = new PhpName("Foo\Bar\Baz", "Bar\Baz");
        $changing = new PhpName("Foo\Bar", "Foo\Bar");

        $this->assertTrue($name->isAffectedByChangesTo($changing));
    }

    public function testRelativeChanges()
    {
        $name = new PhpName("Foo\Bar\Baz", "Bar\Baz");
        $from = new PhpName("Foo\Bar", "Foo\Bar");
        $to = new PhpName("Foo\Baz", "Foo\Baz");

        $newName = $name->change($from, $to);

        $this->assertEquals('Foo\Baz\Baz', $newName->fullyQualifiedName());
        $this->assertEquals('Baz\Baz', $newName->relativeName());
    }
}
