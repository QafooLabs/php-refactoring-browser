<?php

namespace QafooLabs\Refactoring\Domain\Model;

use ArrayIterator;
use IteratorAggregate;

class LineCollection implements IteratorAggregate
{
    /**
     * @var Line[]
     */
    private $lines;

    /**
     * @param Line[] $lines
     */
    public function __construct(array $lines = array())
    {
        $this->lines = $lines;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->lines);
    }

    /**
     * @return Line[]
     */
    public function getLines()
    {
        return $this->lines;
    }

    public function append(Line $line)
    {
        $this->lines[] = $line;
    }

    /**
     * @param string $line
     */
    public function appendString($line)
    {
        $this->append(new Line($line));
    }

    public function appendLines(LineCollection $lines)
    {
        foreach ($lines as $line) {
            $this->append($line);
        }
    }

    public function appendBlankLine()
    {
        $this->lines[] = new Line('');
    }

    /**
     * @param string[] $lines
     *
     * @return LineCollection
     */
    public static function createFromArray(array $lines)
    {
        return new self(array_map(function ($line) {
            return new Line($line);
        }, $lines));
    }

    /**
     * @param string $code
     *
     * @return LineCollection
     */
    public static function createFromString($code)
    {
        return self::createFromArray(explode("\n", $code));
    }
}
