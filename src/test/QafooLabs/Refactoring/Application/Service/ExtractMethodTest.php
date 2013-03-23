<?php

namespace QafooLabs\Refactoring\Application\Service;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;

class ExtractMethodTest extends \PHPUnit_Framework_TestCase
{
    public function testRefactorSimpleMethod()
    {
        $codeanalysis = new StaticCodeAnalysis();
        $scanner = new ParserVariableScanner();
        $refactoring = new ExtractMethod($scanner, $codeanalysis);
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


        $this->assertEquals(<<<'CODE'
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
            , $patch);
    }
}
