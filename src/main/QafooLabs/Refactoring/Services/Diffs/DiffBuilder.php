<?php

namespace QafooLabs\Refactoring\Services\Diffs;

/**
 * Works on the contents of a file and records changes in unified diff format.
 *
 * There can only be one operation per line, otherwise its impossible to
 * guarantee the correctness of a generated diff. The operations allow
 * to implement any possible change:
 *
 * - Append new lines to an existing line (without changing that existing line)
 * - Remove a line
 * - Change a Line by adding one or more replacement lines
 *
 * There is also an aggregate operation that does multiple removes and one
 * change:
 *
 * - Replace multiple lines with new other lines.
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

    /**
     * Append new lines to an original line of the file.
     *
     * @param int $originalLine
     * @param string $lines
     *
     * @return void
     */
    public function appendToLine($originalLine, $lines)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new AppendOperation($originalLine, explode("\n", $lines));
    }

    /**
     * Change one line by replacing it with one or many new lines.
     *
     * @param int $originalLine
     * @param array $newLines
     *
     * @return void
     */
    public function changeLines($originalLine, array $newLines)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new ChangeOperation($originalLine, $newLines);
    }

    /**
     * Remove one line
     *
     * @param int $originalLine
     *
     * @return void
     */
    public function removeLine($originalLine)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new RemoveOperation($originalLine);
    }

    /**
     * Replace a range of lines with a set of new lines.
     *
     * @param int $startOriginalLine
     * @param int $endOrignalLine
     * @param array $newLines
     *
     * @return void
     */
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

    /**
     * Generate a unified diff of all operations performed on the current file.
     *
     * @return string
     */
    public function generateUnifiedDiff()
    {
        if ($this->lines === null) {
            $hunk = Hunk::forEmptyFile();
            return (string)$this->operations[0]->perform($hunk);
        }

        $hunks = array();

        foreach ($this->getLineGroups() as $lineGroup) {
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

    private function getLineGroups()
    {
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

        return $lineGroups;
    }
}
