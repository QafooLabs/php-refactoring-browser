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

namespace QafooLabs\Refactoring\Utils;

use FilterIterator;

/**
 * FilterIterator using callbacks to implement the accept routine.
 */
class CallbackFilterIterator extends FilterIterator
{
    /**
     * @var callable
     */
    private $filter;

    public function __construct($iterator, $filter)
    {
        parent::__construct($iterator);
        $this->filter = $filter;
    }

    public function accept()
    {
        $filter = $this->filter;
        return $filter($this->getInnerIterator()->current());
    }
}

