<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\UseStatement;
use QafooLabs\Refactoring\Domain\Model\LineRange;

class ParserPhpNameScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindNames()
    {
        $file = File::createFromPath(__FILE__, __DIR__);
        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $useStmt = function ($line) use ($file) {
            return new UseStatement($file, LineRange::fromSingleLine($line));
        };

        $this->assertEquals(
            array(
                new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'QafooLabs\Refactoring\Domain\Model\File', $file, 5, $useStmt(5)),
                new PhpName('QafooLabs\Refactoring\Domain\Model\PhpName', 'QafooLabs\Refactoring\Domain\Model\PhpName', $file, 6, $useStmt(6)),
                new PhpName('QafooLabs\Refactoring\Domain\Model\UseStatement', 'QafooLabs\Refactoring\Domain\Model\UseStatement', $file, 7, $useStmt(7)),
                new PhpName('QafooLabs\Refactoring\Domain\Model\LineRange', 'QafooLabs\Refactoring\Domain\Model\LineRange', $file, 8, $useStmt(8)),
                new PhpName('PHPUnit_Framework_TestCase', 'PHPUnit_Framework_TestCase', $file, 10),
                new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'File', $file, 14),
                new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'ParserPhpNameScanner', $file, 15),
            ),
            array_slice($names, 0, 7)
        );
    }

    public function testRegressionFindNamesDetectsFQCNCorrectly() 
    {
        $file = new File("Fqcn.php", <<<'PHP'
<?php

namespace Bar;

class Fqcn
{
    public function main()
    {
        new \Bar\Qux\Adapter($flag);
    }
}
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            array(
                new PhpName('Bar\Qux\Adapter', 'Bar\Qux\Adapter', $file, 9)
            ),
            $names
       );
    }


    public function testFindNamesFindsParentForPhpNameInSingleLineUseStatement()
    {
        $file = new File("Fqcn.php", <<<'PHP'
<?php

use Bar\Qux\Adapter;
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            array(
                new PhpName(
                    'Bar\Qux\Adapter', 
                    'Bar\Qux\Adapter', 
                    $file, 
                    3, 
                    new UseStatement(
                        $file,
                        LineRange::fromSingleLine(3)
                    )
                )
            ),
            $names
       );
    }

    public function testFindNamesFindsParentForPhpNameInMultiLineUseStatement()
    {
        $file = new File("Fqcn.php", <<<'PHP'
<?php

use Bar\Qux\Adapter, 
    Bar\Qux\Foo;
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $use  =  new UseStatement($file, LineRange::fromLines(3, 4));

        $this->assertEquals(
            array(
                new PhpName('Bar\Qux\Adapter', 'Bar\Qux\Adapter', $file, 3, $use),
                new PhpName('Bar\Qux\Foo', 'Bar\Qux\Foo', $file, 4, $use),
            ),
            $names
       );
    }
}
