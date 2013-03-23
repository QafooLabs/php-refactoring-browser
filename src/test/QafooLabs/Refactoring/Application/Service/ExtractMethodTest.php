<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;

class ExtractMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group integration
     */
    public function testRefactorSimpleMethod()
    {
        $applyCommand = \Phake::mock('QafooLabs\Refactoring\Adapters\Patches\ApplyPatchCommand');

        $scanner = new ParserVariableScanner();
        $codeAnalysis = new StaticCodeAnalysis();
        $editor = new PatchEditor($applyCommand);

        $refactoring = new ExtractMethod($scanner, $codeAnalysis, $editor);

        $patch = $refactoring->refactor(new File("foo.php", <<<'PHP'
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


        \Phake::verify($applyCommand)->apply(<<<'CODE'
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
}
