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

/**
 * Representation of a method signature and all its parts (name, visibliity, arguments, returnValues).
 */
class MethodSignature
{
    const IS_PUBLIC = 1;
    const IS_PRIVATE = 2;
    const IS_PROTECTED = 4;
    const IS_STATIC = 8;
    const IS_FINAL = 16;

    private $name;
    private $flags;
    private $arguments;
    private $returnValues;

    public function __construct($name, $flags = self::IS_PRIVATE, array $arguments = array(), $returnValues = array())
    {
        $this->name = $name;
        $this->flags = $this->change($flags);
        $this->arguments = $arguments;
        $this->returnValues = $returnValues;
    }

    private function change($flags)
    {
        $visibility = (self::IS_PRIVATE | self::IS_PROTECTED | self::IS_PUBLIC);
        $allowedVisibilities = array(self::IS_PRIVATE, self::IS_PROTECTED, self::IS_PUBLIC);

        if (($flags & $visibility) === 0) {
            $flags = $flags | self::IS_PRIVATE;
        }

        if ( ! in_array(($flags & $visibility), $allowedVisibilities)) {
            throw new \InvalidArgumentException("Mix of visilibities is not allowed.");
        }

        return $flags;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * Is this method private?
     *
     * @return bool
     */
    public function isPrivate()
    {
        return ($this->flags & self::IS_PRIVATE) > 0;
    }

    /**
     * Is this method static?
     *
     * @return bool
     */
    public function isStatic()
    {
        return ($this->flags & self::IS_STATIC) > 0;
    }
}
