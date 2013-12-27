<?php

namespace QafooLabs\Refactoring\Utils;

use Iterator;

class ToStringIterator implements Iterator
{
    /**
     * @var Iterator
     */
    private $iterator;

    public function __construct(Iterator $it)
    {
        $this->iterator = $it;
    }

    /**
     * @return string
     */
    public function current()
    {
        return (string) $this->iterator->current();
    }

    /**
     * @return scalar
     */
    public function key()
    {
        return $this->iterator->key();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }
}
