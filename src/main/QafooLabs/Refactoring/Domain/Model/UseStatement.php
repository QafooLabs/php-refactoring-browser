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

class UseStatement 
{

    /**
     * @var QafooLabs\Refactoring\Domain\Model\LineRange
     */
    private $declaredLines;

    /**
     * @var QafooLabs\Refactoring\Domain\Model\File
     */
    private $file;

    public function __construct(File $file = null, LineRange $declaredLines = null)
    {
        $this->file = $file;
        $this->declaredLines = $declaredLines;
    }

    public function getEndLine() 
    {
        return $this->declaredLines->getEnd();
    }
}
