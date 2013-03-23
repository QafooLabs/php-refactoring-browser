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
        $this->operations[$originalLine - 1][] = new AppendOperation(explode("\n", $lines));
    }

    public function changeLine($originalLine, $newLine)
    {
        $this->assertLineExists($originalLine);

        $this->operations[$originalLine - 1][] = new ChangeOperation($newLine);
    }

    public function removeLine($originalLine)
    {
        $this->assertLineExists($originalLine);

        $this->operations[$originalLine - 1][] = new RemoveOperation();
    }

    public function generateUnifiedDiff()
    {
        $hunks = array();

        foreach ($this->operations as $line => $lineOperations) {
            if ($this->lines === null) {
                $hunk = Hunk::forEmptyFile();
            } else {
                $hunk = Hunk::forLine($line, $this->lines);
            }

            foreach ($lineOperations as $operation) {
                $hunk = $operation->perform($hunk);
            }

            $hunks[] = $hunk;
        }

        return implode("\n", $hunks);
    }
}
