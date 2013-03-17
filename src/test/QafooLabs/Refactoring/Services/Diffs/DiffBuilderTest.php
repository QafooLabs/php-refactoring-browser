<?php

namespace QafooLabs\Refactoring\Services\Diffs;

class DiffBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyFileAppendLine()
    {
        $builder = new DiffBuilder('');
        $builder->appendToLine(1, 'foo');

        $this->assertEquals(<<<DIFF
@@ -0,0 +1,1 @@
+foo
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testAppendLineInText()
    {
        $builder = new DiffBuilder("foo\nbar\nbaz");
        $builder->appendToLine(2, 'boing');

        $this->assertEquals(<<<DIFF
@@ -1,3 +1,4 @@
 foo
 bar
+boing
 baz
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testAppendLineInBiggerText()
    {
        $builder = new DiffBuilder("foo\nfoo\nbar\nbaz\nbaz");
        $builder->appendToLine(3, 'boing');

        $this->assertEquals(<<<DIFF
@@ -1,5 +1,6 @@
 foo
 foo
 bar
+boing
 baz
 baz
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testChangeLine()
    {
        $builder = new DiffBuilder("foo");
        $builder->changeLine(1, 'boing');

        $this->assertEquals(<<<DIFF
@@ -1,1 +1,1 @@
-foo
+boing
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testChangeAndAppendLine()
    {
        $builder = new DiffBuilder("foo");
        $builder->changeLine(1, 'hello');
        $builder->appendToLine(1, 'world');

        $this->assertEquals(<<<DIFF
@@ -1,1 +1,2 @@
-foo
+hello
+world
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testAppendAndChangeLine()
    {
        $builder = new DiffBuilder("foo");
        $builder->appendToLine(1, 'world');
        $builder->changeLine(1, 'hello');

        $this->assertEquals(<<<DIFF
@@ -1,1 +1,2 @@
-foo
+hello
+world
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testRemoveLine()
    {
        $builder = new DiffBuilder("foo");
        $builder->removeLine(1);

        $this->assertEquals(<<<DIFF
@@ -1,1 +0,0 @@
-foo
DIFF
            , $builder->generateUnifiedDiff());
    }
}
