<?php

namespace QafooLabs\Refactoring\Domain\Model;

use org\bovigo\vfs\vfsStream;

class FileTest extends \PHPUnit_Framework_TestCase
{

    protected $root;

    public function setUp() 
    {
        $this->root = vfsStream::setup('project', 0644, 
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
        $workingDir = $this->root->getChild('src')->url().'/';

        $file = File::createFromPath(
            $this->root->getChild('src')->url().'\Foo\Bar.php', 
            $workingDir
        );

        $this->assertEquals("Foo\Bar.php", $file->getRelativePath());
    }
}
