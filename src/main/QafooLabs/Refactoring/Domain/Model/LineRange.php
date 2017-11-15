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


namespace QafooLabs\Refactoring\Domain\Model;

/**
 * A range of lines.
 */
class LineRange
{
    private $lines = array();

    /**
     * @return LineRange
     */
    static public function fromSingleLine($line)
    {
        $list = new self();
        $list->lines[$line] = $line;

        return $list;
    }

    /**
     * @return LineRange
     */
    static public function fromLines($start, $end)
    {
        $list = new self();

        for ($i = $start; $i <= $end; $i++) {
            $list->lines[$i] = $i;
        }

        return $list;
    }

    /**
     * @return LineRange
     */
    static public function fromString($range)
    {
        list($start, $end) = explode('-', $range);

        return self::fromLines($start, $end);
    }

    public function isInRange($line)
    {
        return isset($this->lines[$line]);
    }

    public function getStart()
    {
        return (int)min($this->lines);
    }

    public function getEnd()
    {
        return (int)max($this->lines);
    }

    public function sliceCode($code)
    {
        $selectedCode = explode("\n", $code);
        $numLines = count($selectedCode);

        for ($i = 0; $i < $numLines; $i++) {
            if ( ! $this->isInRange($i+1)) {
                unset($selectedCode[$i]);
            }
        }

        return array_values($selectedCode);
    }
}
