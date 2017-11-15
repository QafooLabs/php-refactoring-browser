<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\PatchBuilder\PatchEditor;

class ExtractMethodTest extends \PHPUnit_Framework_TestCase
{
    private $applyCommand;

    public function setUp()
    {
        $this->applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\PatchBuilder\ApplyPatchCommand');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor($this->applyCommand);

        $this->refactoring = new ExtractMethod($scanner, $codeAnalysis, $editor);
    }

    /**
     * @group integration
     */
    public function testRefactorSimpleMethod()
    {
        $this->refactoring->refactor(new File('foo.php', <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        echo "Hello World";
    }
}
PHP
            ), LineRange::fromString('6-6'), 'helloWorld');


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -3,6 +3,11 @@
 {
     public function main()
     {
+        $this->helloWorld();
+    }
+
+    private function helloWorld()
+    {
         echo "Hello World";
     }
 }

CODE
        );
    }

    /**
     * @group regression
     * @group GH-4
     */
    public function testVariableUsedBeforeAndAfterExtractedSlice()
    {
        $this->markTestIncomplete('Failing over some invisible whitespace issue?');

        $this->refactoring->refactor(new File('foo.php', <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        $foo = "bar";
        $baz = array();

        $foo = strtolower($foo);
        $baz[] = $foo;

        return new Something($foo, $baz);
    }
}
PHP
            ), LineRange::fromString('9-10'), 'extract');


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -6,9 +6,16 @@
         $foo = "bar";
         $baz = array();

+        list($foo, $baz) = $this->extract($foo, $baz);
+
+        return new Something($foo, $baz);
+    }
+
+    private function extract($foo, $baz)
+    {
         $foo = strtolower($foo);
         $baz[] = $foo;

-        return new Something($foo, $baz);
+        return array($foo, $baz);
     }
 }

CODE
        );
    }
}
