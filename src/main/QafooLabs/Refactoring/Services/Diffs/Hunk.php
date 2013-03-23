<?php

namespace QafooLabs\Refactoring\Services\Diffs;

/**
 * Represents a Hunk in a Diff.
 */
class Hunk
{
    protected $before;
    protected $after;
    protected $lines;
    protected $start;
    protected $size;

    /**
     * Create a Hunk needed when adding lines to an empty file.
     *
     * @return Hunk
     */
    public static function forEmptyFile()
    {
        return new Hunk(array(), array(), array(), 0, 0);
    }

    /**
     * Create a hunk for one given line in a set of lines.
     *
     * @param int $line 0-indexed
     * @param array $fromLines
     *
     * @return Hunk
     */
    public static function forLine($line, array $fromLines)
    {
        $before = self::getLinesBefore($line, $fromLines);
        $after = self::getLinesAfter($line, $fromLines);

        $start = max(1, $line - 2);

        if (isset($fromLines[$line])) {
            $lines = array(' ' . $fromLines[$line]);
        } else {
            $lines = array();
        }

        $size = count($before)+count($after)+count($lines);

        return new Hunk($before, $after, $lines, $start, $size);
    }

    private static function getLinesBefore($line, $lines)
    {
        $num = 2;
        $before = array();

        for ($i = $line; $i > 0 && $i >= ($line-$num); $i--) {
            $before[] = ' ' . $lines[$i - 1];
        }

        return array_reverse($before);
    }

    private static function getLinesAfter($line, $lines)
    {
        $num = 2;
        $after = array();

        for ($i = $line+1; $i < count($lines) && $i <= ($line+$num); $i++) {
            $after[] =  ' ' . $lines[$i];
        }

        return $after;
    }

    private function __construct(array $before, array $after, array $lines, $start, $size)
    {
        $this->before = $before;
        $this->after = $after;
        $this->lines = $lines;
        $this->start = $start;
        $this->size = $size;
    }

    public function removeLines()
    {
        return $this->newLines(array('-' . ltrim($this->lines[0])));
    }

    public function appendLines(array $appendLines)
    {
        return $this->newLines(array_merge(
            $this->lines,
            array_map(function($line) {
                return '+' . $line;
            }, $appendLines)));
    }

    public function changeLines($newLine)
    {
        return $this->newLines(array_merge(
            array(
                '-' . ltrim($this->lines[0]),
                '+' . $newLine,
            ),
            array_slice($this->lines, 1)
        ));
    }

    private function newLines(array $newLines)
    {
        return new Hunk($this->before, $this->after, $newLines, $this->start, $this->size);
    }

    public function __toString()
    {
        $diff = implode("\n", array_merge(
            $this->before,
            $this->lines,
            $this->after
        ));

        $newFileHunkRange = count(array_filter($this->lines, function ($line) {
            return substr($line, 0, 1) !== '-';
        })) + count($this->before)+count($this->after);

        $newFileStart = max(1, $this->start);
        if ($newFileHunkRange === 0) {
            $newFileStart--;
        }

        $context = "";
        $hunk = sprintf("@@ -%s,%s +%s,%s @@%s",
            $this->start, $this->size, $newFileStart, $newFileHunkRange, $context);

        return $hunk . "\n" . $diff;
    }
}
