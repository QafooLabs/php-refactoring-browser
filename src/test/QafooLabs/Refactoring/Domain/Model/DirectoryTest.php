<?php

namespace QafooLabs\Refactoring\Domain\Model;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFindAllPhpFilesRecursivly()
    {
        $directory = new Directory(__DIR__, __DIR__);
        $files = $directory->findAllPhpFilesRecursivly();

        $this->assertContainsOnly('QafooLabs\Refactoring\Domain\Model\File', $files);
    }

    public function testRemovesDuplicates()
    {
        vfsStreamWrapper::register();

        $structure = array(
            'src' => array(
                'src' => array(),
                'Foo' => array(
                    'src' => array(),
                    'Foo' => array(),
                    'Bar.php' => '<?php'
                ),
            )
        );

        vfsStream::create($structure, VfsStream::setup('project'));
        $dir = VfsStream::url('project/src');

        $directory = new Directory($dir, $dir);
        $files = $directory->findAllPhpFilesRecursivly();

        $foundFiles = array();
        foreach ($files as $f => $file) {
            $foundFiles[] = $f;
        }

        $this->assertEquals(array('vfs://project/src/Foo/Bar.php'), $foundFiles);
    }
}
