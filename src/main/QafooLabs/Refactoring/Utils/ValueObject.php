<?php

namespace QafooLabs\Refactoring\Utils;

/**
 * Value Objects allow access to private/protected data with PHP magic get.
 *
 * Layer Supertype for Value Objects.
 */
abstract class ValueObject
{
    final public function __set($name, $value)
    {
        throw new \BadmethodCallException("Cannot set values on a value object.");
    }

    public function __get($name)
    {
        if ( ! isset($this->$name)) {
            throw new \RuntimeException(sprintf("Variable %s does not exist on %s.", $name,  get_class($this)));
        }

        return $this->$name;
    }
}
