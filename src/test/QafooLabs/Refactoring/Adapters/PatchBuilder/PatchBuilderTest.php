<?php

namespace QafooLabs\Refactoring\Adapters\PatchBuilder;

class PatchBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PatchBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new PatchBuilder(
            "line1\n" .
            "line2\n" .
            "line3\n" .
            "line4\n" .
            "line5\n" .
            "line6\n" .
            "line7\n" .
            "line8\n" .
            "line9\n"
        );
    }

    public function testChangeTokenOnLineAlone()
    {
        $this->builder->changeToken(4, 'line4', 'linefour');

        $expected = <<<DIFF
--- a/
+++ b/
@@ -1,7 +1,7 @@
 line1
 line2
 line3
-line4
+linefour
 line5
 line6
 line7

DIFF;

        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testChangeIsCaseSensitive()
    {
        $this->builder = new PatchBuilder('$bar = new Bar();');
        $this->builder->changeToken(1, 'Bar', 'Foo');

        $expected = <<<DIFF
--- a/
+++ b/
@@ -1,1 +1,1 @@
-\$bar = new Bar();
+\$bar = new Foo();

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testChangeTokenAloneOnIndentedLine()
    {
        $this->builder = new PatchBuilder(
            "line1\n" .
            "    line2\n" .
            "line3\n"
        );

        $this->builder->changeToken(2, 'line2', 'linetwo');

        $expected = <<<DIFF
--- a/
+++ b/
@@ -1,3 +1,3 @@
 line1
-    line2
+    linetwo
 line3

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testChangeTokenWithMultipleTokensOneOneLine()
    {
        $this->builder = new PatchBuilder(
            "line1\n" .
            "    echo \$var . ' = ' . \$var;\n" .
            "line3\n"
        );

        $this->builder->changeToken(2, 'var', 'variable');

        $expected = <<<DIFF
--- a/
+++ b/
@@ -1,3 +1,3 @@
 line1
-    echo \$var . ' = ' . \$var;
+    echo \$variable . ' = ' . \$variable;
 line3

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testChangeTokenWithUnderscore()
    {
        $this->builder = new PatchBuilder(
            "line1\n" .
            "    echo \$my_variable;\n" .
            "line3\n"
        );

        $this->builder->changeToken(2, 'my_variable', 'myVariable');

        $expected = <<<DIFF
--- a/
+++ b/
@@ -1,3 +1,3 @@
 line1
-    echo \$my_variable;
+    echo \$myVariable;
 line3

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testAppendToLine()
    {
        $this->builder->appendToLine(5, array('line5.1', 'line5.2'));

        $expected = <<<DIFF
--- a/
+++ b/
@@ -3,6 +3,8 @@
 line3
 line4
 line5
+line5.1
+line5.2
 line6
 line7
 line8

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testChangeLines()
    {
        $this->builder->changeLines(5, array('linefive', 'linefive.five'));

        $expected = <<<DIFF
--- a/
+++ b/
@@ -2,7 +2,8 @@
 line2
 line3
 line4
-line5
+linefive
+linefive.five
 line6
 line7
 line8

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testRemoveLine()
    {
        $this->builder->removeLine(5);

        $expected = <<<DIFF
--- a/
+++ b/
@@ -2,7 +2,6 @@
 line2
 line3
 line4
-line5
 line6
 line7
 line8

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testReplaceLines()
    {
        $this->builder->replaceLines(4, 6, array('hello', 'world'));

        $expected = <<<DIFF
--- a/
+++ b/
@@ -1,9 +1,8 @@
 line1
 line2
 line3
-line4
-line5
-line6
+hello
+world
 line7
 line8
 line9

DIFF;
        $this->assertEquals($expected, $this->builder->generateUnifiedDiff());
    }

    public function testGetOriginalLines()
    {
        $this->assertEquals(
            array('line4', 'line5', 'line6'),
            $this->builder->getOriginalLines(4, 6)
        );
    }
}
