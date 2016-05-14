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

use QafooLabs\Collections\Hashable;

/**
 * Representation of a Name in PHP
 */
class PhpName implements Hashable
{
    const TYPE_NAMESPACE = 1;
    const TYPE_USE       = 2;
    const TYPE_CLASS     = 3;
    const TYPE_USAGE     = 4;

    private $fullyQualifiedName;
    private $relativeName;
    private $type;

    static public function createDeclarationName($fullyQualifiedName)
    {
        $parts = self::stringToParts($fullyQualifiedName);

        return new PhpName(
            $fullyQualifiedName,
            end($parts),
            self::TYPE_CLASS
        );
    }

    public function __construct($fullyQualifiedName, $relativeName, $type = self::TYPE_USAGE)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->relativeName = $relativeName;
        $this->type = $type;
    }

    /**
     * Would this name be affected by a change to the given name?
     *
     * @param PhpName $other
     * @return bool
     */
    public function isAffectedByChangesTo(PhpName $other)
    {
        return
            $this->fullyQualifiedName === $other->fullyQualifiedName ||
            $this->overlaps($other)
        ;
    }

    private function overlaps(PhpName $other)
    {
        $otherParts = self::stringToParts($other->fullyQualifiedName);
        $thisParts = self::stringToParts($this->fullyQualifiedName);

        $otherLength = count($otherParts) - 1;
        $otherRelativeLength = count(self::stringToParts($other->relativeName));
        $thisRelativeStart = count($thisParts) - count(self::stringToParts($this->relativeName)) - 1;

        $matches = array();

        for ($i = $otherLength; $i > ($otherLength - $otherRelativeLength) && $i > $thisRelativeStart; $i--) {
            if (isset($thisParts[$i])) {
                $matches[] = $thisParts[$i] === $otherParts[$i];
            }
        }

        if ($matches) {
            return count($matches) === count(array_filter($matches));
        }

        return false;
    }

    private function shareNamespace(PhpName $other)
    {
        $otherParts = $this->stringToParts($other->fullyQualifiedName);

        return strpos($this->fullyQualifiedName, $otherParts[0]) !== false;
    }

    public function change(PhpName $from, PhpName $to)
    {
        if ( ! $this->isAffectedByChangesTo($from) && ! $this->shareNamespace($from)) {
            return $this;
        }

        if ($this->equals($from)) {
            return $to;
        }

        $newParts = self::stringToParts($to->fullyQualifiedName);
        $newParts = $this->adjustSize($from, $newParts);

        if ($this->isFullyQualified()) {
            $relativeNewParts = $newParts;
        } else {
            $diff = ($this->type === self::TYPE_CLASS)
                ? 0
                : count($newParts) - $this->numParts();

            $relativeNewParts = array_slice($newParts, -1 * (count(explode('\\', $this->relativeName))+$diff));
        }

        return new PhpName(self::partsToString($newParts), self::partsToString($relativeNewParts), $this->type);
    }

    private function numParts()
    {
        return substr_count($this->fullyQualifiedName, '\\') + 1;
    }

    private function adjustSize($from, $newParts)
    {
        $fromParts = self::stringToParts($from->fullyQualifiedName);
        $thisParts = self::stringToParts($this->fullyQualifiedName);
        $sizeChange = count($fromParts) - count($newParts);

        if (count($thisParts) > (count($newParts)+$sizeChange)) {
            $newParts = array_merge($newParts, array_slice($thisParts, count($newParts)+$sizeChange));
        }

        if (count($thisParts) < (count($newParts)+$sizeChange)) {
            $newParts = array_slice($newParts, 0, count($thisParts) - $sizeChange);
        }

        return $newParts;
    }

    public function fullyQualifiedName()
    {
        return $this->fullyQualifiedName;
    }

    public function namespaceName()
    {
        $parts = self::stringToParts($this->fullyQualifiedName);
        array_pop($parts);

        $name = self::partsToString($parts);

        return new PhpName($name, $name, self::TYPE_NAMESPACE);
    }

    public function shortName()
    {
        $parts = self::stringToParts($this->fullyQualifiedName);

        return array_pop($parts);
    }

    public function relativeName()
    {
        return $this->relativeName;
    }

    /**
     * @return bool
     */
    public function equals(PhpName $other)
    {
        return $this->fullyQualifiedName === $other->fullyQualifiedName &&
               $this->relativeName === $other->relativeName;
    }

    public function __toString()
    {
        return sprintf('%s[%s]', $this->fullyQualifiedName, $this->relativeName);
    }

    public function hashCode()
    {
        return '1373136332' . $this->fullyQualifiedName . $this->relativeName;
    }

    public function fullyQualified()
    {
        return new PhpName($this->fullyQualifiedName, $this->fullyQualifiedName, $this->type);
    }

    static private function partsToString($parts)
    {
        return implode('\\', $parts);
    }

    static private function stringToParts($string)
    {
        return explode('\\', $string);
    }

    public function type()
    {
        return $this->type;
    }

    /**
     * Is the relative name fully qualified ?
     *
     * @return bool
     */
    public function isFullyQualified()
    {
        return $this->fullyQualifiedName === $this->relativeName;
    }

    /**
     * Is the php name found in a use statement?
     *
     * @return bool
     */
    public function isUse()
    {
        return $this->type === PhpName::TYPE_USE;
    }
}
