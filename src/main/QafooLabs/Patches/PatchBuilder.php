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
 * Build patch for contents of a file and records operations in unified diff format.
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
class PatchBuilder
{
    /**
     * @var array|null
     */
    private $lines = null;

    /**
     * @var array
     */
    private $operations = array();

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $contents
     * @param string $path
     */
    public function __construct($contents, $path = null)
    {
        if ( ! empty($contents)) {
            $this->lines = explode("\n", rtrim($contents));
        }
        $this->path = $path;
    }

    /**
     * Change Token in given line from old to new.
     *
     * @param int $originalLine
     * @param string $oldToken
     * @param string $newToken
     *
     * @return void
     */
    public function changeToken($originalLine, $oldToken, $newToken)
    {
        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new ChangeTokenOperation($originalLine, $oldToken, $newToken);
    }

    /**
     * Append new lines to an original line of the file.
     *
     * @param int $originalLine
     * @param array $lines
     *
     * @return void
     */
    public function appendToLine($originalLine, array $lines)
    {
        if (count($lines) === 0) {
            throw new \InvalidArgumentException("No lines were passed to the append operation. At least one required.");
        }

        $this->assertValidOriginalLine($originalLine);

        $this->operations[$originalLine] = new AppendOperation($originalLine, $lines);
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
        if (count($newLines) === 0) {
            throw new \InvalidArgumentException("No lines were passed to the change operation. At least one required.");
        }

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
     * @param int $endOriginalLine
     * @param array $newLines
     *
     * @return void
     */
    public function replaceLines($startOriginalLine, $endOriginalLine, array $newLines)
    {
        $this->assertValidOriginalLine($startOriginalLine);
        $this->assertValidOriginalLine($endOriginalLine);

        if ($startOriginalLine > $endOriginalLine) {
            throw new \InvalidArgumentException("Start line number has to be smaller than end original line.");
        }

        for ($i = $startOriginalLine; $i <= $endOriginalLine; $i++) {
            if ($i === $endOriginalLine) {
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
            throw new \RuntimeException(sprintf("Adding more than one operation to line %d is not allowed in path %s.", $originalLine, $this->path));
        }
    }

    /**
     * Generate a unified diff of all operations performed on the current file.
     *
     * @return string
     */
    public function generateUnifiedDiff()
    {
        if ( ! $this->operations) {
            return "";
        }

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

        $output = "";

        if ($this->path) {
            $output .= "--- a/" . $this->path . "\n";
            $output .= "+++ b/" . $this->path . "\n";
        }

        $output .= implode("\n", $hunks);

        return $output;
    }

    private function getLineGroups()
    {
        $affectedLines = array_keys($this->operations);
        sort($affectedLines);

        $lineGroups = array();
        $currentLineGroup = array(array_shift($affectedLines));

        foreach ($affectedLines as $affectedLine) {
            if ($affectedLine - max($currentLineGroup) < 4) {
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
