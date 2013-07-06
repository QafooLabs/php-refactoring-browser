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

class PhpNameChange
{
    private $fromName;
    private $toName;

    public function __construct(PhpName $fromName, PhpName $toName)
    {
        $this->fromName = $fromName;
        $this->toName = $toName;
    }

    public function affects(PhpName $name)
    {
        return $name->isAffectedByChangesTo($this->fromName);
    }

    public function change(PhpName $name)
    {
        return $name->change($this->fromName, $this->toName);
    }
}
