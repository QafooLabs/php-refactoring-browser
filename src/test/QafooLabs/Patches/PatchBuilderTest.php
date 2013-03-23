<?php

namespace QafooLabs\Patches;

class PatchBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyFileAppendLine()
    {
        $builder = new PatchBuilder('');
        $builder->appendToLine(0, array('foo'));

        $this->assertEquals(<<<DIFF
@@ -0,0 +1,1 @@
+foo
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testAppendNoLines_ThrowsException()
    {
        $builder = new PatchBuilder('');

        $this->setExpectedException('InvalidArgumentException');
        $builder->appendToLine(0, array());
    }

    public function testAppendLineInText()
    {
        $builder = new PatchBuilder("foo\nbar\nbaz");
        $builder->appendToLine(2, array('boing'));

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
        $builder = new PatchBuilder("foo\nfoo\nbar\nbaz\nbaz");
        $builder->appendToLine(3, array('boing'));

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
        $builder = new PatchBuilder("foo");
        $builder->changeLines(1, array('boing'));

        $this->assertEquals(<<<DIFF
@@ -1,1 +1,1 @@
-foo
+boing
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testChangeLine_NoNewLines_throwsException()
    {
        $builder = new PatchBuilder("foo");

        $this->setExpectedException("InvalidArgumentException");
        $builder->changeLines(1, array());
    }

    public function testRemoveLine()
    {
        $builder = new PatchBuilder("foo");
        $builder->removeLine(1);

        $this->assertEquals(<<<DIFF
@@ -1,1 +0,0 @@
-foo
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testRemoveLineInbetween()
    {
        $builder = new PatchBuilder("foo\nfoo\nbar\nbar\nbar");
        $builder->removeLine(4);

        $this->assertEquals(<<<DIFF
@@ -2,4 +2,3 @@
 foo
 bar
-bar
 bar
DIFF
            , $builder->generateUnifiedDiff());
    }

    public function testRemoveNonExistantLine()
    {
        $builder = new PatchBuilder("foo");

        $this->setExpectedException('QafooLabs\Patches\UnknownLineException', 'Accessed non existing line 10 in code.');
        $builder->removeLine(10);
    }

    public function testChangeNonExistantLine()
    {
        $builder = new PatchBuilder("foo");

        $this->setExpectedException('QafooLabs\Patches\UnknownLineException', 'Accessed non existing line 10 in code.');
        $builder->changeLines(10, array('foo'));
    }

    public function testReplaceLines()
    {
        $builder = new PatchBuilder("foo\nfoo\nbar\nbar\nbar");
        $builder->replaceLines(1, 5, array("Hello World!"));

        $this->assertEquals(<<<DIFF
@@ -1,5 +1,1 @@
-foo
-foo
-bar
-bar
-bar
+Hello World!
DIFF
            , $builder->generateUnifiedDiff());
    }
}
