<?php

namespace QafooLabs\Collections;

use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * Unique Set of Elements
 */
class Set implements Countable, IteratorAggregate
{
    private $items = array();

    /**
     * Add a new item to the set.
     *
     * Overrides values that were previously set that have the same value
     * or hashCode. If you pass an object make sure it implements either
     * {__toString()} or the {@see Hashable} interface.
     *
     * @param mixed $item
     * @return void
     */
    public function add($item)
    {
        if ($item instanceof Hashable) {
            $this->items[$item->hashCode()] = $item;

            return;
        }

        $this->items[(string)$item] = $item;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator(array_values($this->items));
    }
}
