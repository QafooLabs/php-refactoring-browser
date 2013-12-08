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

namespace QafooLabs\Refactoring\Adapters\Patches;

use QafooLabs\Refactoring\Domain\Model\EditorBuffer;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Patches\PatchBuilder;

class PatchBuffer implements EditorBuffer
{
    /**
     * @var \QafooLabs\Patches\PatchBuilder
     */
    private $builder;

    public function __construct(PatchBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getLines(LineRange $range)
    {
        return $this->builder->getLines($range->getStart(), $range->getEnd());
    }

    public function replace(LineRange $range, array $newLines)
    {
        $this->builder->replaceLines($range->getStart(), $range->getEnd(), $newLines);
    }

    public function append($line, array $newLines)
    {
        $this->builder->appendToLine($line, $newLines);
    }

    public function replaceString($line, $oldToken, $newToken)
    {
        if ($oldToken === $newToken) {
            return;
        }

        $this->builder->changeToken($line, $oldToken, $newToken);
    }
}
