<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;

class ExtractMethodTest extends \PHPUnit_Framework_TestCase
{
    private $applyCommand;

    public function setUp()
    {
        $this->applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\Patches\ApplyPatchCommand');

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
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        echo "Hello World";
    }
}
PHP
            ), LineRange::fromString("6-6"), "helloWorld");


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -4,5 +4,10 @@
     public function main()
     {
-        echo "Hello World";
+        $this->helloWorld();
     }
+
+    private function helloWorld()
+    {
+        echo "Hello World";
+    }
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
        $patch = $this->refactoring->refactor(new File("foo.php", <<<'PHP'
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
            ), LineRange::fromString("9-10"), "extract");


        \Phake::verify($this->applyCommand)->apply(<<<'CODE'
--- a/foo.php
+++ b/foo.php
@@ -7,8 +7,15 @@
         $baz = array();
 
-        $foo = strtolower($foo);
-        $baz[] = $foo;
+        list($foo, $baz) = $this->extract($foo, $baz);
 
         return new Something($foo, $baz);
     }
+
+    private function extract($foo, $baz)
+    {
+        $foo = strtolower($foo);
+        $baz[] = $foo;
+
+        return array($foo, $baz);
+    }
 }
CODE
        );
    }
}
