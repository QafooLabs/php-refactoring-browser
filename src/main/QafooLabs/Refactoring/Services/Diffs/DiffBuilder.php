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

    private function assertValidOriginalLine($originalLine)
    {
        Assertion::integer($originalLine);

        if ( $originalLine !== 0 && ! isset($this->lines[$originalLine - 1])) {
            throw new UnknownLineException($originalLine);
        }

        if (isset($this->operations[$originalLine])) {
            throw new \RuntimeException(sprintf("Adding more than one operation to line %d is not allowed.", $originalLine));
        }
    }

    public function appendToLine($originalLine, $lines)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new AppendOperation($originalLine, explode("\n", $lines));
    }

    public function changeLines($originalLine, array $newLines)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new ChangeOperation($originalLine, $newLines);
    }

    public function removeLine($originalLine)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new RemoveOperation($originalLine);
    }

    public function replaceLines($startOriginalLine, $endOrignalLine, array $newLines)
    {
        $this->assertValidOriginalLine($startOriginalLine);
        $this->assertValidOriginalLine($endOrignalLine);

        if ($startOriginalLine > $endOrignalLine) {
            throw new \InvalidArgumentException("Start line number has to be smaller than end original line.");
        }

        for ($i = $startOriginalLine; $i <= $endOrignalLine; $i++) {
            if ($i === $endOrignalLine) {
                $this->changeLines($i, $newLines);
            } else {
                $this->removeLine($i);
            }
        }
    }

    public function generateUnifiedDiff()
    {
        if ($this->lines === null) {
            $hunk = Hunk::forEmptyFile();
            return (string)$this->operations[0]->perform($hunk);
        }

        $hunks = array();

        $affectedLines = array_keys($this->operations);
        sort($affectedLines);

        $lineGroups = array();
        $currentLineGroup = array(array_shift($affectedLines));

        foreach ($affectedLines as $affectedLine) {
            if ($affectedLine - max($currentLineGroup) < 3) {
                $currentLineGroup[] = $affectedLine;
            } else {
                $lineGroups[] = $currentLineGroup;
                $currentLineGroup = array($affectedLine);
            }
        }
        $lineGroups[] = $currentLineGroup;

        foreach ($lineGroups as $lineGroup) {
            $start = min($lineGroup);
            $end = max($lineGroup);

            $hunk = Hunk::forLines($start, $end, $this->lines);

            foreach ($this->operations as $line => $operation) {
                if ( ! in_array($line, $lineGroup)) {
                    continue;
                }

                $hunk = $operation->perform($hunk);
            }

            $hunks[] = $hunk;
        }

        return implode("\n", $hunks);
    }
}
