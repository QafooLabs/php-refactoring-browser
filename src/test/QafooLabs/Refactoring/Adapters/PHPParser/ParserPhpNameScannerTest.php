<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpName;
use QafooLabs\Refactoring\Domain\Model\PhpNameOccurance;

class ParserPhpNameScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindNames()
    {
        $file = File::createFromPath(__FILE__, __DIR__);
        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            array(
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser', 'QafooLabs\Refactoring\Adapters\PHPParser', PhpName::TYPE_NAMESPACE), $file, 3),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'QafooLabs\Refactoring\Domain\Model\File', PhpName::TYPE_USE), $file, 5),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\PhpName', 'QafooLabs\Refactoring\Domain\Model\PhpName', PhpName::TYPE_USE), $file, 6),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\PhpNameOccurance', 'QafooLabs\Refactoring\Domain\Model\PhpNameOccurance', PhpName::TYPE_USE), $file, 7),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScannerTest', 'ParserPhpNameScannerTest', PhpName::TYPE_CLASS), $file, 9),
                new PhpNameOccurance(new PhpName('PHPUnit_Framework_TestCase', 'PHPUnit_Framework_TestCase', PhpName::TYPE_USAGE), $file, 9),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'File', PhpName::TYPE_USAGE), $file, 13),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'ParserPhpNameScanner', PhpName::TYPE_USAGE), $file, 14),
            ),
            array_slice($names, 0, 8)
        );
    }

    public function testRegressionFindNamesDetectsFQCNCorrectly()
    {
        $file = new File('Fqcn.php', <<<'PHP'
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
        $names = array_values(array_filter(
            $scanner->findNames($file),
            function ($occurance) {
                return $occurance->name()->type() === PhpName::TYPE_USAGE;
            }
        ));

        $this->assertEquals(
            array(
                new PhpNameOccurance(
                    new PhpName('Bar\Qux\Adapter', 'Bar\Qux\Adapter'),
                    $file,
                    9
                )
            ),
            $names
       );
    }


    public function testFindNamesFindsParentForPhpNameInSingleLineUseStatement()
    {
        $file = new File('Fqcn.php', <<<'PHP'
<?php

use Bar\Qux\Adapter;
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            array(
                new PhpNameOccurance(
                    new PhpName(
                        'Bar\Qux\Adapter',
                        'Bar\Qux\Adapter',
                        PhpName::TYPE_USE
                    ),
                    $file,
                    3
                )
            ),
            $names
       );
    }

    public function testFindNamesFindsParentForPhpNameInMultiLineUseStatement()
    {
        $file = new File('Fqcn.php', <<<'PHP'
<?php

use Bar\Qux\Adapter,
    Bar\Qux\Foo;
PHP
        );

        $scanner = new ParserPhpNameScanner();
        $names = $scanner->findNames($file);

        $this->assertEquals(
            array(
                new PhpNameOccurance(
                    new PhpName('Bar\Qux\Adapter', 'Bar\Qux\Adapter', PhpName::TYPE_USE),
                    $file,
                    3
                ),
                new PhpNameOccurance(
                    new PhpName('Bar\Qux\Foo', 'Bar\Qux\Foo', PhpName::TYPE_USE),
                    $file,
                    4
                )
            ),
            $names
       );
    }
}
