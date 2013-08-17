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
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\PHPUnit_Framework_TestCase', 'PHPUnit_Framework_TestCase', PhpName::TYPE_USAGE), $file, 9),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'File', PhpName::TYPE_USAGE), $file, 13),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'ParserPhpNameScanner', PhpName::TYPE_USAGE), $file, 14),
            ),
            array_slice($names, 0, 8)
        );
    }
}

