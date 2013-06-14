<?php

namespace QafooLabs\Refactoring\Adapters\PHPParser;

use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpUseStatement;

class ParserUseStatementScannerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindUseStatements()
    {
        $file = File::createFromPath(__FILE__, __DIR__);
        $scanner = new ParserUseStatementScanner();
        $uses = $scanner->findUseStatements($file);

        $this->assertEquals(
            array(
                new PhpUseStatement($file, 'QafooLabs\Refactoring\Domain\Model\File', 5),
                new PhpUseStatement($file, 'QafooLabs\Refactoring\Domain\Model\PhpUseStatement', 6),
            ),
            $uses
        );
    }
}
