<?php

namespace QafooLabs\Refactoring\Services\Diffs;

/**
 * Works on the contents of a file and records changes in unified diff format.
 */
class DiffBuilder
{
    const OPERATION_APPEND = 'append';
    const OPERATION_CHANGE = 'change';

    private $lines = null;

    private $operations = array();

    public function __construct($contents)
    {
        if ( ! empty($contents)) {
            $this->lines = explode("\n", $contents);
        }
    }

    public function appendToLine($originalLine, $lines)
    {
        $this->operations[$originalLine][] = array(
            'type' => self::OPERATION_APPEND,
            'lines' => explode("\n", $lines),
        );
    }

    public function changeLine($originalLine, $newLine)
    {
        $this->operations[$originalLine][] = array(
            'type' => self::OPERATION_CHANGE,
            'newLine' => $newLine,
        );
    }

    public function generateUnifiedDiff()
    {
        $hunks = array();

        foreach ($this->operations as $line => $lineOperations) {
            $line--;

            if ($this->lines === null) {
                $before = $after = $lines = array();
                $start = 0;
                $size = 0;
            } else {

                $before = $this->getLinesBefore($line, 2);
                $after = $this->getLinesAfter($line, 2);

                $start = max(1, $line - 2);

                if (isset($this->lines[$line])) {
                    $lines = array(' ' . $this->lines[$line]);
                } else {
                    $lines = array();
                }

                $size = count($before)+count($after)+count($lines);
            }

            foreach ($lineOperations as $operation) {
                if ($operation['type'] === self::OPERATION_APPEND) {
                    $lines = array_merge(
                        $lines,
                        array_map(function($x) {
                            return '+' . $x;
                        }, $operation['lines']));
                } else if ($operation['type'] === self::OPERATION_CHANGE) {
                    $lines = array_merge(
                        array(
                            '-' . ltrim($lines[0]),
                            '+' . $operation['newLine'],
                        ),
                        array_slice($lines, 1)
                    );
                }
            }

            $diff = implode("\n", array_merge(
                $before,
                $lines,
                $after
            ));

            $newFileHunkRange = count(array_filter($lines, function ($x) {
                return substr($x, 0, 1) !== '-';
            })) + count($before)+count($after);

            $context = "";
            $hunk = sprintf("@@ -%s,%s +%s,%s @@%s",
                $start, $size, max(1, $start), $newFileHunkRange, $context);

            $hunks = array($hunk . "\n" . $diff);
        }

        return implode("\n", $hunks);
    }

    private function getLinesBefore($line, $num)
    {
        $before = array();

        for ($i = $line; $i > 0 && $i >= ($line-$num); $i--) {
            $before[] = ' ' . $this->lines[$i - 1];
        }

        return $before;
    }

    private function getLinesAfter($line, $num)
    {
        $after = array();

        for ($i = $line+1; $i < count($this->lines) && $i <= ($line+$num); $i++) {
            $after[] =  ' ' . $this->lines[$i];
        }

        return $after;

    }
}
