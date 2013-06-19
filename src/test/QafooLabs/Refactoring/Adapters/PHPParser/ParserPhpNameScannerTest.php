<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;

class ParserPhpNameScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindNames()
    {
        $file = File::createFromPath(__FILE__, __DIR__);
        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            array(
                new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'QafooLabs\Refactoring\Domain\Model\File', $file, 5),
                new PhpName('QafooLabs\Refactoring\Domain\Model\PhpName', 'QafooLabs\Refactoring\Domain\Model\PhpName', $file, 6),
                new PhpName('PHPUnit_Framework_TestCase', 'PHPUnit_Framework_TestCase', $file, 8),
                new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'File', $file, 12),
                new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'ParserPhpNameScanner', $file, 13),
            ),
            array_slice($names, 0, 5)
        );
    }

    public function testRegressionFindNamesDetectsFQCNCorrectly() {
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
}

