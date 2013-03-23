<?php

namespace QafooLabs\Refactoring\Services\Diffs;

/**
 * Works on the contents of a file and records changes in unified diff format.
 */
class DiffBuilder
{
    private $lines = null;

    private $operations = array();

    public function __construct($contents)
    {
        if ( ! empty($contents)) {
            $this->lines = explode("\n", $contents);
        }
    }

    private function assertLineExists($line)
    {
        if ( ! isset($this->lines[$line - 1])) {
            throw new UnknownLineException($line);
        }
    }

    public function appendToLine($originalLine, $lines)
    {
        $this->operations[$originalLine] = new AppendOperation($originalLine, explode("\n", $lines));
    }

    public function changeLines($originalLine, array $newLines)
    {
        $this->assertLineExists($originalLine);

        $this->operations[$originalLine] = new ChangeOperation($originalLine, $newLines);
    }

    public function removeLine($originalLine)
    {
        $this->assertLineExists($originalLine);

        $this->operations[$originalLine] = new RemoveOperation();
    }

    public function generateUnifiedDiff()
    {
        $hunks = array();

        foreach ($this->operations as $line => $operation) {
            if ($this->lines === null) {
                $hunk = Hunk::forEmptyFile();
            } else {
                $hunk = Hunk::forLine($line, $this->lines);
            }

            $hunks[] = $operation->perform($hunk);
        }

        return implode("\n", $hunks);
    }
}
