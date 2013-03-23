<?php

namespace QafooLabs\Patches;

class HunkTest extends \PHPUnit_Framework_TestCase
{
    public function testForEmptyFile()
    {
        $hunk = Hunk::forEmptyFile();

        $this->assertEquals("@@ -0,0 +0,0 @@\n", (string)$hunk);
    }

    public function testAppendLines()
    {
        $hunk = Hunk::forEmptyFile();
        $newHunk = $hunk->appendLines(0, array("foo"));

        $this->assertEquals("@@ -0,0 +1,1 @@\n+foo", (string)$newHunk);
    }

    public function testForLineWithoutChange()
    {
        $hunk = Hunk::forLine(1, array("foo"));

        $this->assertEquals("@@ -1,1 +1,1 @@\n foo", (string)$hunk);
    }

    public function testForLineWithContextWithoutChange()
    {
        $hunk = Hunk::forLine(3, array("foo", "foo", "bar", "baz", "baz"));

        $this->assertEquals(<<<'HUNK'
@@ -1,5 +1,5 @@
 foo
 foo
 bar
 baz
 baz
HUNK
            , (string)$hunk);
    }

    public function testForLineAppend()
    {
        $hunk = Hunk::forLine(3, array("foo", "foo", "bar", "baz", "baz"));
        $newHunk = $hunk->appendLines(3, array("bar"));

        $this->assertEquals(<<<'HUNK'
@@ -1,5 +1,6 @@
 foo
 foo
 bar
+bar
 baz
 baz
HUNK
            , (string)$newHunk);
    }

    public function testForLineRemove()
    {
        $hunk = Hunk::forLine(3, array("foo", "foo", "bar", "baz", "baz"));
        $newHunk = $hunk->removeLine(3);

        $this->assertEquals(<<<'HUNK'
@@ -1,5 +1,4 @@
 foo
 foo
-bar
 baz
 baz
HUNK
            , (string)$newHunk);
    }

    public function testForLineChangeLines()
    {
        $hunk = Hunk::forLine(3, array("foo", "foo", "bar", "baz", "baz"));
        $newHunk = $hunk->changeLines(3, array("lol"));

        $this->assertEquals(<<<'HUNK'
@@ -1,5 +1,5 @@
 foo
 foo
-bar
+lol
 baz
 baz
HUNK
            , (string)$newHunk);
    }

    public function testForLines()
    {
        $hunk = Hunk::forLines(1, 2, array("foo", "bar"));
        $this->assertEquals("@@ -1,2 +1,2 @@\n foo\n bar", (string)$hunk);
    }

    public function testForLinesWithBeforeAndAfter()
    {
        $hunk = Hunk::forLines(3, 4, array("foo", "foo", "bar", "bar", "baz", "baz"));

        $this->assertEquals(<<<'HUNK'
@@ -1,6 +1,6 @@
 foo
 foo
 bar
 bar
 baz
 baz
HUNK
            , (string)$hunk);
    }

    public function testForLinesAppend()
    {
        $hunk = Hunk::forLines(3, 4, array("foo", "foo", "bar", "bar", "baz", "baz"));
        $hunk = $hunk->appendLines(3, array("hello"));
        $hunk = $hunk->appendLines(4, array("world"));

        $this->assertEquals(<<<'HUNK'
@@ -1,6 +1,8 @@
 foo
 foo
 bar
+hello
 bar
+world
 baz
 baz
HUNK
            , (string)$hunk);
    }
}
