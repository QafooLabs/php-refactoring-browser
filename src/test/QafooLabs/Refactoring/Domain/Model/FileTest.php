<?php

namespace QafooLabs\Refactoring\Domain\Model;

use org\bovigo\vfs\vfsStream;

class FileTest extends \PHPUnit_Framework_TestCase
{
    private function createFileSystem()
    {
        return vfsStream::setup('project', 0644,
            array(
                'src'=>
                array(
                    'Foo'=>
                    array(
                        'Bar.php'=>'<?php noop() ?>'
                    )
                )
            )
        );
    }

    public function testGetRelativePathRespectsMixedWindowsPathsAndWorkingDirectoryTrailingSlashs()
    {
        $root = $this->createFileSystem();
        $workingDir = $root->getChild('src')->url().'/';

        $file = File::createFromPath(
            $root->getChild('src')->url().'\Foo\Bar.php',
            $workingDir
        );

        $this->assertEquals("Foo\Bar.php", $file->getRelativePath());
    }

    public function testRelativePathConstructionForAbsoluteVFSFiles()
    {
        $src = $this->createFileSystem()->getChild('src')->url();
        $bar = $src.DIRECTORY_SEPARATOR.'Foo'.DIRECTORY_SEPARATOR.'Bar.php';

        $file = File::createFromPath($bar, $notRelatedWorkingDir = __DIR__);
        $this->assertEquals('vfs://project/src/Foo/Bar.php', $file->getRelativePath());
    }

    static public function dataExtractPsr0ClassName()
    {
        return array(
            array(new PhpName('Foo', 'Foo'), 'src'.DIRECTORY_SEPARATOR.'Foo.php'),
            array(new PhpName('Foo\Bar', 'Bar'), 'src'.DIRECTORY_SEPARATOR.'Foo'.DIRECTORY_SEPARATOR.'Bar.php'),
        );
    }

    /**
     * @dataProvider dataExtractPsr0ClassName
     */
    public function testExtractPsr0ClassName($expectedClassName, $fileName)
    {
        $file = new File($fileName, '<?php');
        $actualClassName = $file->extractPsr0ClassName();

        $this->assertTrue($expectedClassName->equals($actualClassName), "- $expectedClassName\n+ $actualClassName");
    }
}
