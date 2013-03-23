<?php

namespace QafooLabs\Refactoring\Adapters\TokenReflection;

use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;

class StaticCodeAnalysis implements CodeAnalysis
{
    private $broker;

    public function __construct()
    {
        // caching in memory gives us error for now :(
    }

    public function isMethodStatic(File $file, LineRange $range)
    {
        $this->broker = new \TokenReflection\Broker(new \TokenReflection\Broker\Backend\Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        $lastLine = $range->getEnd();

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->geTclasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        return $method->isStatic();
                    }
                }
            }
        }

        return false;
    }

    public function getMethodEndLine(File $file, LineRange $range)
    {
        $this->broker = new \TokenReflection\Broker(new \TokenReflection\Broker\Backend\Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        $endLineClass = 0;
        $lastLine = $range->getEnd();

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->geTclasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        return $method->getEndLine();
                    }
                }

                $endLineClass = $class->getEndLine() - 1;
            }
        }

        return $endLineClass;
    }
}
