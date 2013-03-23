<?php

namespace QafooLabs\Refactoring\Application;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Adapters\PHPParser\ParserVariableScanner;
use QafooLabs\Refactoring\Adapters\TokenReflection\StaticCodeAnalysis;
use QafooLabs\Refactoring\Adapters\Patches\PatchEditor;

class RenameLocalVariableTest extends \PHPUnit_Framework_TestCase
{
    public function testRenameLocalVariable()
    {
        $scanner = \Phake::mock('QafooLabs\Refactoring\Domain\Services\VariableScanner');
        $codeAnalysis = \Phake::mock('QafooLabs\Refactoring\Domain\Services\CodeAnalysis');
        $editor = \Phake::mock('QafooLabs\Refactoring\Domain\Services\Editor');

        $refactoring = new RenameLocalVariable($scanner, $codeAnalysis, $editor);

        $patch = $refactoring->refactor(new File("foo.php", <<<'PHP'
<?php
class Foo
{
    public function main()
    {
        $helloWorld = 'bar';
    }
}
PHP
            ), 6, '$helloWorld', '$var');
    }
}
