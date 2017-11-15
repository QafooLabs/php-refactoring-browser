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
 * Defined properties that are used or assigned.
 */
class DefinedProperties
{
    /**
     * Name of properties that are declared.
     *
     * @var array
     */
    protected $declarations;

    /**
     * Name of properties that are used.
     *
     * @var array
     */
    protected $usages;

    public function __construct(array $declarations = array(), array $usages = array())
    {
        $this->declarations = $declarations;
        $this->usages = $usages;
    }

    public function declaration($property)
    {
        if (!isset($this->declarations[$property])) {
            return 0;
        }

        return $this->declarations[$property];
    }

    public function usages($property)
    {
        if (!isset($this->usages[$property])) {
            return array();
        }

        return array_unique($this->usages[$property]);
    }

    /**
     * Does list contain the given variable?
     *
     * @return bool
     */
    public function contains(Variable $variable)
    {
        return (
            isset($this->declarations[$variable->getName()]) ||
            isset($this->usages[$variable->getName()])
        );
    }
}
