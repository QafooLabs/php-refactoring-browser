<?php

namespace QafooLabs\Refactoring\Domain\Model;

class PhpClassNameTest extends \PHPUnit_Framework_TestCase
{
    public function testNames()
    {
        $className = new PhpClassName(File::createFromPath(__FILE__, realpath(__DIR__ . "/../../../../")));

        $this->assertEquals("PhpClassNameTest", $className->getShortname());
        $this->assertEquals("QafooLabs\Refactoring\Domain\Model", $className->getNamespace());
    }
}
