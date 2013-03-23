<?php

namespace QafooLabs\Refactoring\Services\Diffs;

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
        $newHunk = $hunk->appendLines(array("foo"));

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
        $hunk = Hunk::forLine(2, array("foo", "foo", "bar", "baz", "baz"));
        $newHunk = $hunk->appendLines(array("bar"));

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
        $hunk = Hunk::forLine(2, array("foo", "foo", "bar", "baz", "baz"));
        $newHunk = $hunk->removeLines();

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
        $hunk = Hunk::forLine(2, array("foo", "foo", "bar", "baz", "baz"));
        $newHunk = $hunk->changeLines("lol");

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
}
