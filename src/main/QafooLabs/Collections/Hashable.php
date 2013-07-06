<?php

namespace QafooLabs\Collections;

/**
 * Allows to span a hash over all identifing values of an object.
 *
 * This is used by some data structures to compute the uniqueness of
 * an object, for example in the Set.
 */
interface Hashable
{
    /**
     * Return a hash over identifying values of this object.
     *
     * @return string
     */
    public function hashCode();
}
