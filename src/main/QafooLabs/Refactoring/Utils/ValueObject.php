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
