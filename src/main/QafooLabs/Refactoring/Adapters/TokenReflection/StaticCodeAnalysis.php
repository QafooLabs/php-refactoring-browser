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
use QafooLabs\Refactoring\Domain\Model\PhpName;

use TokenReflection\Broker;
use TokenReflection\Broker\Backend\Memory;
use TokenReflection\ReflectionNamespace;

class StaticCodeAnalysis extends CodeAnalysis
{
    private $broker;

    public function __construct()
    {
        // caching in memory gives us error for now :(
    }

    public function isMethodStatic(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        return $method ? $method->isStatic() : false;
    }

    public function getMethodEndLine(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        if ($method === null) {
            throw new \InvalidArgumentException("Could not find method end line.");
        }

        return $method->getEndLine();
    }

    public function getMethodStartLine(File $file, LineRange $range)
    {
        $method = $this->findMatchingMethod($file, $range);

        if ($method === null) {
            throw new \InvalidArgumentException("Could not find method start line.");
        }

        return $method->getStartLine();
    }

    public function getFunctionEndLine(File $file, LineRange $range)
    {
        $function = $this->findMatchingFunction($file, $range);

        if ($function === null) {
            throw new \InvalidArgumentException("Could not find function end line.");
        }

        return $function->getEndLine();
    }

    public function getFunctionStartLine(File $file, LineRange $range)
    {
        $function = $this->findMatchingFunction($file, $range);

        if ($function === null) {
            throw new \InvalidArgumentException("Could not find function start line.");
        }

        return $function->getStartLine();
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
        return $this->findMatchingMethod($file, $range) !== null;
    }

    public function isInsideFunction(File $file, LineRange $range)
    {
        return $this->findMatchingFunction($file, $range) !== null;
    }

    public function isLocalScope(File $file, LineRange $range)
    {
        return $this->isInsideMethod($file, $range) || $this->isInsideFunction($file, $range);
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
            $noNamespace = ReflectionNamespace::NO_NAMESPACE_NAME === $namespace->getName();
            foreach ($namespace->getClasses() as $class) {
                $classes[] = new PhpClass(
                    PhpName::createDeclarationName($class->getName()),
                    $class->getStartLine(),
                    $noNamespace ? 0 : $namespace->getStartLine()
                );
            }
        }

        return $classes;
    }

    private function findMatchingMethod(File $file, LineRange $range)
    {
        $foundMethod = null;

        $this->broker = new Broker(new Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        $lastLine = $range->getEnd();

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                foreach ($class->getMethods() as $method) {
                    if ($method->getStartLine() < $lastLine && $lastLine < $method->getEndLine()) {
                        $foundMethod = $method;
                        break;
                    }
                }
            }
        }

        return $foundMethod;
    }

    private function findMatchingFunction(File $file, LineRange $range)
    {
        $foundFunction = null;

        $this->broker = new Broker(new Memory);
        $file = $this->broker->processString($file->getCode(), $file->getRelativePath(), true);
        $lastLine = $range->getEnd();

        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getFunctions() as $function) {
                if ($function->getStartLine() < $lastLine && $lastLine < $function->getEndLine()) {
                    $foundFunction = $function;
                    break;
                }
            }
        }

        return $foundFunction;
    }
}
