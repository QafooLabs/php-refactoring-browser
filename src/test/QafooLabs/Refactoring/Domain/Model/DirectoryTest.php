<?php

namespace QafooLabs\Refactoring\Domain\Model;

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFindAllPhpFilesRecursivly()
    {
        $directory = new Directory(__DIR__, __DIR__);
        $files = $directory->findAllPhpFilesRecursivly();

        $this->assertContainsOnly('QafooLabs\Refactoring\Domain\Model\File', $files);
    }
}
