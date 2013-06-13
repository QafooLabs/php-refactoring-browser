<?php

namespace QafooLabs\Refactoring\Domain\Model;

class PhpClassName
{
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function getNamespace()
    {
        return $this->expectedClassNamespace($this->file);
    }

    public function getShortname()
    {
        return $this->expectedClassShortNameIn($this->file);
    }

    private function expectedClassNamespace(File $phpFile)
    {
        $parts = explode("/", $phpFile->getRelativePath());
        $namespace = array();

        foreach ($parts as $part) {
            if ($this->startsWithLowerCase($part)) {
                $namespace = array();
                continue;
            }

            $namespace[] = $part;
        }

        array_pop($namespace);

        return str_replace(".php", "", implode("\\", $namespace));
    }

    private function startsWithLowerCase($string)
    {
        return strtolower($string[0]) === $string[0];
    }

    private function expectedClassShortNameIn(File $phpFile)
    {
        return str_replace(".php", "", $phpFile->getBasename());
    }
}
