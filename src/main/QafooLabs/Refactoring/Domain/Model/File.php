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
        $workingDirectory = rtrim($workingDirectory, '/\\');
        $relativePath = ltrim(str_replace($workingDirectory, "", $path), "/\\");

        return new self($relativePath, $code);
    }

    public function __construct($relativePath, $code)
    {
        $this->relativePath = $relativePath;
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getBaseName()
    {
        return basename($this->relativePath);
    }

    /**
     * Extract the PhpName for the class contained in this file assuming PSR-0 naming.
     *
     * @return PhpName
     */
    public function extractPsr0ClassName()
    {
        $shortName = $this->parseFileForPsr0ClassShortName();

        return new PhpName(
            ltrim($this->parseFileForPsr0NamespaceName() . '\\' . $shortName, '\\'),
            $shortName
        );
    }

    private function parseFileForPsr0ClassShortName()
    {
        return str_replace(".php", "", $this->getBaseName());
    }

    private function parseFileForPsr0NamespaceName()
    {
        $file = ltrim($this->getRelativePath(), DIRECTORY_SEPARATOR);

        if (preg_match('(^([a-z]+:\/\/))', $file, $matches)) {
            $file = substr($file, strlen($matches[1]));
        }

        $parts = explode(DIRECTORY_SEPARATOR, ltrim($this->getRelativePath(), DIRECTORY_SEPARATOR));
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
        return isset($string[0]) && strtolower($string[0]) === $string[0];
    }
}

