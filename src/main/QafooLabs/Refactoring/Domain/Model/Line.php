<?php

namespace QafooLabs\Refactoring\Domain\Model;

class Line
{
    /**
     * @var string
     */
    private $line;

    /**
     * @param string $line
     */
    public function __construct($line)
    {
        $this->line = (string) $line;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->line;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return trim($this->line) === '';
    }

    /**
     * @return int
     */
    public function getIndentation()
    {
        return strlen($this->line) - strlen(ltrim($this->line));
    }
}
