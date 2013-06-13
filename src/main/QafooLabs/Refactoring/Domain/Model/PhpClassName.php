<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Abstraction fo php class names based on a file.
 */
class PhpClassName
{
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function getNamespace()
    {
        $parts = explode("/", $this->file->getRelativePath());
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

    public function getShortname()
    {
        return str_replace(".php", "", $this->file->getBasename());
    }

    private function startsWithLowerCase($string)
    {
        return strtolower($string[0]) === $string[0];
    }
}
