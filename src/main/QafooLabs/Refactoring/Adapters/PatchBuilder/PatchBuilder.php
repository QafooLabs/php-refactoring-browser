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

namespace QafooLabs\Refactoring\Adapters\PatchBuilder;

use TomPHP\PatchBuilder\Builder\PhpDiffBuilder;
use TomPHP\PatchBuilder\PatchBuffer as PatchBuilderBuffer;
use TomPHP\PatchBuilder\Types\LineRange;
use TomPHP\PatchBuilder\Types\OriginalLineNumber;

/**
 * Provides a gateway through to the PatchBuilderLibrary.
 */
class PatchBuilder
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var PatchBuilderBuffer
     */
    private $buffer;

    /**
     * @param string $contents
     * @param string $path
     */
    public function __construct($contents, $path = null)
    {
        $lines = array();

        if ( ! empty($contents)) {
            $lines = explode("\n", rtrim($contents));
        }

        $this->buffer = PatchBuilderBuffer::createWithContents($lines);

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
        $newLine = $this->buffer->getLine($this->createLineNumber($originalLine));

        $newLine = preg_replace(
            '!(^|[^a-z0-9])(' . preg_quote($oldToken) . ')([^a-z0-9]|$)!',
            '\1' . $newToken . '\3',
            $newLine
        );

        $this->buffer->replace(
            $this->createSingleLineRange($originalLine),
            array($newLine)
        );
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
        // Why is this one method different to the rest?
        $originalLine++;

        $this->buffer->insert($this->createLineNumber($originalLine), $lines);
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
        $this->buffer->replace($this->createSingleLineRange($originalLine), $newLines);
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
        $this->buffer->delete($this->createSingleLineRange($originalLine));
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
        $this->buffer->replace($this->createLineRange($startOriginalLine, $endOriginalLine), $newLines);
    }

    /**
     * Generate a unified diff of all operations performed on the current file.
     *
     * @return string
     */
    public function generateUnifiedDiff()
    {
        $builder = new PhpDiffBuilder();

        return $builder->buildPatch(
            'a/' . $this->path,
            'b/' . $this->path,
            $this->buffer
        );
    }

    /**
     * @param int $start
     * @param int $end
     *
     * @return LineRange
     */
    private function createLineRange($start, $end)
    {
        return new LineRange(
            new OriginalLineNumber($start - 1),
            new OriginalLineNumber($end - 1)
        );
    }

    /**
     * @param int $number
     *
     * @return LineRange
     */
    private function createSingleLineRange($number)
    {
        $line = new OriginalLineNumber($number - 1);

        return new LineRange($line, $line);
    }

    /**
     * @param int $number
     *
     * @return OriginalLineNumber
     */
    private function createLineNumber($number)
    {
        return new OriginalLineNumber($number - 1);
    }
}
