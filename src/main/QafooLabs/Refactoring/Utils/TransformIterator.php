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

use Traversable;
use Iterator;

abstract class TransformIterator implements Iterator
{
    /**
     * @var Traversable
     */
    private $iterator;

    public function __construct(Traversable $iterator)
    {
        $this->iterator = $iterator;
    }

    abstract protected function transform($value);

    public function next()
    {
        return $this->iterator->next();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function current()
    {
        return $this->transform($this->iterator->current());
    }

    public function rewind()
    {
        return $this->iterator->rewind();
    }

    public function key()
    {
        return $this->iterator->key();
    }
}

