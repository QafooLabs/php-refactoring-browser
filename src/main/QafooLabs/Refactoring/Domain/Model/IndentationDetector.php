<?php

namespace QafooLabs\Refactoring\Domain\Model;

class IndentationDetector
{
    /**
     * @var LineCollection
     */
    private $lines;

    public function __construct(LineCollection $lines)
    {
        $this->lines = $lines;
    }

    /**
     * @return int
     */
    public function getMinIndentation()
    {
        return array_reduce(
            iterator_to_array($this->lines),
            function ($minIndentation, $line) {
                $indentation = $line->getIndentation();

                if ($line->isEmpty()) {
                    return $minIndentation;
                }

                if ($minIndentation === null) {
                    return $indentation;
                }

                return min($minIndentation, $indentation);
            }
        );
    }

    /**
     * @return int
     */
    public function getFirstLineIndentation()
    {
        $indentation = null;

        foreach ($this->lines as $line) {
            if (!$line->isEmpty()) {
                $indentation = $line->getIndentation();
                break;
            }
        }

        return $indentation;
    }
}
