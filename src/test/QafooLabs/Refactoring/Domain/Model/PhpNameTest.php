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

    public function testRegression()
    {
        $name = new PhpName("Bar\Bar", "Bar\Bar");
        $changing = new PhpName("Bar", "Bar");

        $this->assertTrue($name->isAffectedByChangesTo($changing));
    }

    public function testRegression2()
    {
        $name = new PhpName("Foo\\Foo", "Foo\\Foo");
        $from = new PhpName("Foo\\Foo", "Foo");
        $to = new PhpName("Foo\\Bar", "Bar");

        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\\Bar', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\\Bar', $changed->relativeName());
    }

    public function testRegression3()
    {
        $name = new PhpName("Foo\\Foo", "Foo\\Foo");
        $from = new PhpName("Foo\\Foo", "Foo\\Foo");
        $to = new PhpName("Foo\\Bar\\Foo", "Foo\\Bar\\Foo");

        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\\Bar\\Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\\Bar\\Foo', $changed->relativeName());
    }

    public function testCreateDeclarationName()
    {
        $name = PhpName::createDeclarationName('Foo\Bar\Baz');

        $this->assertEquals('Foo\Bar\Baz', $name->fullyQualifiedName());
        $this->assertEquals('Baz', $name->relativeName());
        $this->assertEquals(PhpName::TYPE_CLASS, $name->type());
    }

    public function testRegression4()
    {
        $name = new PhpName('Foo', 'Foo');
        $from = new PhpName('Foo\\Foo', 'Foo');
        $to = new PhpName('Foo\\Bar', 'Bar');

        $this->assertFalse($name->isAffectedByChangesTo($from), "Namespace should not be affected by changes to Class in namespace.");
    }

    public function testRegression5()
    {
        $from = new PhpName("Qafoo\ChangeTrack\ChangeFeed", "Qafoo\ChangeTrack\ChangeFeed");
        $to = new PhpName("Qafoo\ChangeTrack\Analyzer\ChangeFeed", "Qafoo\ChangeTrack\Analyzer\ChangeFeed");
        $name = new PhpName("Qafoo\ChangeTrack\ChangeFeed", "ChangeFeed");

        $changed = $name->change($from, $to);

        $this->assertEquals('Qafoo\ChangeTrack\Analyzer\ChangeFeed', $changed->fullyQualifiedName());
        $this->assertEquals('Analyzer\ChangeFeed', $changed->relativeName());
    }

    public function testRegression6()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo');
        $to = new PhpName('Foo\Bar\Baz\Boing', 'Boing');

        $name = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\Bar\Baz\Boing', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\Bar\Baz\Boing', $changed->relativeName());
    }

    public function testRegression7()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo');
        $to = new PhpName('Foo\Boing', 'Boing');

        $name = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $changed = $name->change($from, $to);

        $this->assertEquals('Foo\Boing', $changed->fullyQualifiedName());
        $this->assertEquals('Foo\Boing', $changed->relativeName());
    }

    public function testRegression8()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $to = new PhpName('Foo\Boing', 'Foo\Boing');

        $name = new PhpName('Foo\Foo', 'Foo\Foo');
        $changed = $name->change($from, $to);

        $this->assertEquals('Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Foo', $changed->relativeName());
    }

    public function testChangeKeepsType()
    {
        $from = new PhpName('Foo\Foo\Foo', 'Foo\Foo\Foo');
        $to = new PhpName('Foo\Boing', 'Foo\Boing');

        $name = new PhpName('Foo\Foo', 'Foo\Foo', PhpName::TYPE_NAMESPACE);
        $changed = $name->change($from, $to);

        $this->assertEquals(PhpName::TYPE_NAMESPACE, $changed->type());
    }

    public function testAddRelativeNameWhenNamespaceExpands()
    {
        $from = new PhpName('Foo', 'Foo');
        $to = new PhpName('Foo\Bar', 'Foo\Bar');

        $name = new PhpName('Foo\Foo', 'Foo');
        $changed = $name->change($from, $to);

        $this->assertFalse($name->isAffectedByChangesTo($from));
        $this->assertEquals('Foo\Bar\Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Bar\Foo', $changed->relativeName());
    }

    public function testNotExpandWhenRelativeNameIsTypeClass()
    {
        $from = new PhpName('Foo', 'Foo');
        $to = new PhpName('Foo\Bar', 'Foo\Bar');

        $name = new PhpName('Foo\Foo', 'Foo', PhpName::TYPE_CLASS);
        $changed = $name->change($from, $to);

        $this->assertFalse($name->isAffectedByChangesTo($from));
        $this->assertEquals('Foo\Bar\Foo', $changed->fullyQualifiedName());
        $this->assertEquals('Foo', $changed->relativeName());
    }

    /**
     * @dataProvider provideIsFullyQualified
     */
    public function testIsFullyQualified($fqcn, $relativeName, $expected = TRUE)
    {
        $name = new PHPName($fqcn, $relativeName);

        $this->assertEquals($expected, $name->isFullyQualified());
    }

    public static function provideIsFullyQualified()
    {
        $tests = array();

        $tests[] = array('Foo', 'Foo', TRUE);
        $tests[] = array('Foo\\Bar\\Foo', 'Foo\\Bar\\Foo', TRUE);

        $tests[] = array('Foo\\Bar\\Foo', 'Foo', FALSE);
        $tests[] = array('Foo\\Bar\\Foo', 'Bar\\Foo', FALSE);

        return $tests;
    }

    public function testGetShortNameReturnsLastPartForFQCN()
    {
        $name = new PhpName('Foo\\Bar', "Foo\\Bar", null, null);
        $short = new PhpName("Foo", "Foo", null, null);

        $this->assertEquals('Bar', $name->shortName());
        $this->assertEquals('Foo', $short->shortName());
    }

    public function testIsUseStatementWhenParentIsAUseStatement()
    {
        $name = new PhpName('Foo\\Bar', 'Foo\\Bar', PhpName::TYPE_USE);

        $this->assertTrue($name->isUse());
    }
}
