<?php

namespace QafooLabs\Refactoring\Domain\Model;

class IndentingLineCollection extends LineCollection
{
    const INDENTATION_SIZE = 4;

    /**
     * @var int
     */
    private $indentation = 0;

    public function addIndentation()
    {
        $this->indentation++;
    }

    public function removeIndentation()
    {
        $this->indentation--;
    }

    public function append(Line $line)
    {
        parent::append(new Line($this->createIndentationString() . (string) $line));
    }

    /**
     * @return string
     */
    private function createIndentationString()
    {
        return str_repeat(' ', $this->indentation * self::INDENTATION_SIZE);
    }
}
