<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Represent a file in the project being refactored.
 */
class File
{
    private $relativePath;
    private $code;

    /**
     * @param string $path
     * @param string $workingDirectory
     *
     * @return File
     */
    public static function createFromPath($path, $workingDirectory)
    {
        if ( ! file_exists($path) || ! is_file($path)) {
            throw new \InvalidArgumentException("Not a valid file: " . $path);
        }

        $code = file_get_contents($path);
        $relativePath = ltrim(str_replace($workingDirectory, "", $path), "/");

        return new self($relativePath, $code);
    }

    public function __construct($relativePath, $code)
    {
        $this->relativePath = $relativePath;
        $this->code = $code;
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public function getCode()
    {
        return $this->code;
    }
}
