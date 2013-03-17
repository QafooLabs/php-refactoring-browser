<?php

namespace QafooLabs\Refactoring\Services\Diffs;

/**
 * Works on the contents of a file and records changes in unified diff format.
 */
class DiffBuilder
{
    const OPERATION_APPEND = 'append';

    private $lines = array();

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

    public function generateUnifiedDiff()
    {
        $hunks = array();

        foreach ($this->operations as $line => $lineOperations) {
            $line--;

            $before = $this->getLinesBefore($line, 2);
            $after = $this->getLinesAfter($line, 2);

            $size = count($before)+count($after);
            $start = max(0, $line - 2);

            if (isset($this->lines[$line])) {
                $lines = array(' ' . $this->lines[$line]);
            } else {
                $lines = arraY();
            }

            foreach ($lineOperations as $operation) {
                if ($operation['type'] === self::OPERATION_APPEND) {
                    $lines = array_merge(
                        $lines,
                        array_map(function($x) {
                            return '+' . $x;
                        }, $operation['lines']));
                }
            }

            $diff = implode("\n", array_merge(
                $before,
                $lines,
                $after
            ));

            $context = "";
            $hunk = sprintf("@@ -%s,%s +%s,%s @@%s",
                $start, $size, max(1, $start), count($lines), $context);

            $hunks = array($hunk . "\n" . $diff);
        }

        return implode("\n", $hunks);
    }

    private function getLinesBefore($line, $num)
    {
        $before = array();

        for ($i = $line-1; $i > 0 && $i > ($line-$num); $i--) {
            $before[] = ' ' . $this->lines[$i];
        }

        return $before;
    }

    private function getLinesAfter($line, $num)
    {
        $after = array();

        for ($i = $line+1; $i < count($this->lines) && $i < ($line+$num); $i++) {
            $after[] =  ' ' . $this->lines[$i];
        }

        return $after;

    }
}
