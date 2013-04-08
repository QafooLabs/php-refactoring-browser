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

namespace QafooLabs\Patches;

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
        return self::forLines($line, $line, $fromLines);
    }

    public static function forLines($start, $end, array $fromLines)
    {
        Assertion::integer($start, "Start line number has to be an integer.");
        Assertion::integer($end, "End line number has to be an integer.");

        if ($start > $end) {
            throw new \InvalidArgumentException("Start line number should be smaller than End line number.");
        }

        $startLine = max(1, $start - 2);
        $start--;
        $end--;

        $before = self::getLinesBefore($start, $fromLines);
        $after = self::getLinesAfter($end, $fromLines);

        $lines = array();
        for ($i = 0; $i <= ($end-$start); $i++) {
            if (isset($fromLines[$start + $i])) { // TODO: WHY?
                $lines[] = ' ' . $fromLines[$start + $i];
            }
        }

        $size = count($before)+count($after)+count($lines);

        return new Hunk($before, $after, $lines, $startLine, $size);
    }

    private static function getLinesBefore($line, $lines)
    {
        $num = 2;
        $before = array();

        for ($i = $line; $i > 0 && $i > ($line-$num); $i--) {
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

    public function removeLine($originalLine)
    {
        return $this->changeLines($originalLine, array());
    }

    private function getRelativeLine($originalLine)
    {
        // Additions to the original lines have to be taken into account.
        // Deletions replace the original line so are not relevant.
        $additions = count(array_filter($this->lines, function ($line) { return substr($line, 0, 1) === '+'; }));

        if ($originalLine === $this->start) {
            return $additions;
        }

        $relativeLine = $originalLine - $this->start + $additions - count($this->before);

        if ( ! isset($this->lines[$relativeLine])) {
            throw new \InvalidArgumentException(sprintf("Line %d is not part of the editable lines of this hunk.", $originalLine));
        }

        return $relativeLine;
    }

    public function appendLines($originalLine, array $appendLines)
    {
        if ($originalLine === 0) {
            return $this->newLines($this->markLinesAdd($appendLines));
        }

        $relativeLine = $this->getRelativeLine($originalLine);
        $beforeLines = array_slice($this->lines, 0, $relativeLine + 1);
        $afterLines = array_slice($this->lines, $relativeLine + 1);

        return $this->newLines(array_merge(
            $beforeLines,
            $this->markLinesAdd($appendLines),
            $afterLines
        ));
    }

    private function markLinesAdd(array $lines)
    {
        return array_map(function ($line) {
            return '+' . $line;
        }, $lines);
    }

    public function changeLines($originalLine, array $newLines)
    {
        $relativeLine = $this->getRelativeLine($originalLine);
        $beforeLines = array_slice($this->lines, 0, $relativeLine);
        $afterLines = array_slice($this->lines, $relativeLine + 1);

        return $this->newLines(array_merge(
            $beforeLines,
            array('-' . substr($this->lines[$relativeLine], 1)),
            $this->markLinesAdd($newLines),
            $afterLines
        ));
    }

    public function changeToken($originalLine, $oldToken, $newToken)
    {
        $relativeLine = $this->getRelativeLine($originalLine);
        $beforeLines = array_slice($this->lines, 0, $relativeLine);
        $afterLines = array_slice($this->lines, $relativeLine + 1);

        $newLine = substr($this->lines[$relativeLine], 1);
        $newLine = preg_replace(
            '(([^a-zA-Z0-9]?)(' . preg_quote($oldToken) . ')([^[a-zA-Z0-9]?))',
            '\1' . $newToken . '\3',
            $newLine
        );

        return $this->newLines(array_merge(
            $beforeLines,
            array('-' . substr($this->lines[$relativeLine], 1)),
            array('+' . $newLine),
            $afterLines
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
