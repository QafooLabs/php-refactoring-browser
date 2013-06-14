<?php
/**
 * Qafoo PHP Refactoring Browser
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */


namespace QafooLabs\Refactoring\Adapters\TokenReflection;

use QafooLabs\Refactoring\Domain\Services\CodeAnalysis;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\PhpClass;

use TokenReflection\Broker;
use TokenReflection\Broker\Backend\Memory;

class StaticCodeAnalysis extends CodeAnalysis
{
    private $broker;

    public function __construct()
    {
        // caching in memory gives us error for now :(
    }

    public function isMethodStatic(File $file, LineRange $range)
    {
        $this->broker = new Broker(new Memory);
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
        $this->broker = new Broker(new Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        $lastLine = $range->getEnd();

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->geTclasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        return $method->getEndLine();
                    }
                }
            }
        }

        throw new \InvalidArgumentException("Could not find method end line.");
    }

    public function getMethodStartLine(File $file, LineRange $range)
    {
        $this->broker = new Broker(new Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        $lastLine = $range->getEnd();

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->geTclasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        return $method->getStartLine();
                    }
                }
            }
        }

        throw new \InvalidArgumentException("Could not find method start line.");
    }

    public function getLineOfLastPropertyDefinedInScope(File $file, $lastLine)
    {
        $this->broker = new Broker(new Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                $lastPropertyDefinitionLine = $class->getStartLine() + 1;

                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        foreach ($class->getProperties() as $property) {
                            $lastPropertyDefinitionLine = max($lastPropertyDefinitionLine, $property->getEndLine());
                        }

                        return $lastPropertyDefinitionLine;
                    }
                }
            }
        }

        throw new \InvalidArgumentException("Could not find method start line.");
    }

    public function isInsideMethod(File $file, LineRange $range)
    {
        $this->broker = new Broker(new Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $range->getStart() && $range->getEnd() < $method->getEndLine()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param File $file
     * @return PhpClass[]
     */
    public function findClasses(File $file)
    {
        $classes = array();

        $this->broker = new Broker(new Memory);

        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                $classes[] = new PhpClass($class->getName(), $class->getStartLine(), $namespace->getStartLine());
            }
        }

        return $classes;
    }
}
