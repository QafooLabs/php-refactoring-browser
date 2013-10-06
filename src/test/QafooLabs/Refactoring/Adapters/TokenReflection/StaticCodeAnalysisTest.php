<?php

namespace QafooLabs\Refactoring\Adapters\TokenReflection;

use PHPUnit_Framework_TestCase;
use QafooLabs\Refactoring\Domain\Model\File;

class StaticCodeAnalysisTest extends PHPUnit_Framework_TestCase 
{
    public function testNamespaceDeclarationForFileWithoutNamespace_isInLine0()
    {
        $file = new File('without-namespace.php', <<<'PHP'
<?php

class WithoutNamespace
{

}
PHP
        );

        $analysis = new StaticCodeAnalysis();
        $classes = $analysis->findClasses($file);
        $class = $classes[0];

        $this->assertEquals(0, $class->namespaceDeclarationLine(), 'namespace declaration line for file without namespace');
    }
}
