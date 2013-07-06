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
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'QafooLabs\Refactoring\Domain\Model\File'), $file, 5),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\PhpName', 'QafooLabs\Refactoring\Domain\Model\PhpName'), $file, 6),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\PhpNameOccurance', 'QafooLabs\Refactoring\Domain\Model\PhpNameOccurance'), $file, 7),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\PHPUnit_Framework_TestCase', 'PHPUnit_Framework_TestCase'), $file, 9),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Domain\Model\File', 'File'), $file, 13),
                new PhpNameOccurance(new PhpName('QafooLabs\Refactoring\Adapters\PHPParser\ParserPhpNameScanner', 'ParserPhpNameScanner'), $file, 14),
            ),
            array_slice($names, 0, 6)
        );
    }
}

