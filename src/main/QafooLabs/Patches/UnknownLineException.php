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

use InvalidArgumentException;

class UnknownLineException extends InvalidArgumentException
{
    public function __construct($line)
    {
        parent::__construct(sprintf('Accessed non existing line %d in code.', $line));
    }
}
