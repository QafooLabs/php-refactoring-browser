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
    private $fullyQualifiedName;
    private $relativeName;

    static public function createDeclarationName($fullyQualifiedName)
    {
        $parts = explode("\\", $fullyQualifiedName);

        return new PhpName(
            $fullyQualifiedName,
            end($parts)
        );
    }

    public function __construct($fullyQualifiedName, $relativeName)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->relativeName = $relativeName;
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
        $otherParts = explode("\\", $other->fullyQualifiedName);
        $thisParts = explode("\\", $this->fullyQualifiedName);

        $otherLength = count($otherParts) - 1;
        $otherRelativeLength = count(explode("\\", $other->relativeName));
        $thisRelativeStart = count($thisParts) - count(explode("\\", $this->relativeName)) - 1;

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

    public function change(PhpName $from, PhpName $to)
    {
        if ( ! $this->isAffectedByChangesTo($from)) {
            return $this;
        }

        if ($this->equals($from)) {
            return $to;
        }

        $toParts = explode("\\",   $to->fullyQualifiedName);
        $thisParts = explode("\\", $this->fullyQualifiedName);

        $newParts = array();
        for ($i = 0; $i < count($thisParts); $i++) {
            if ( ! isset($toParts[$i])) {
                $newParts[] = $thisParts[$i];
            } else {
                $newParts[] = $toParts[$i];
            }
        }

        $relativeNewParts = array_slice($newParts, -1 * count(explode('\\', $this->relativeName)));
        return new PhpName(implode('\\', $newParts), implode('\\', $relativeNewParts));
    }

    public function fullyQualifiedName()
    {
        return $this->fullyQualifiedName;
    }

    public function namespaceName()
    {
        $parts = explode("\\", $this->fullyQualifiedName);
        array_pop($parts);

        return implode("\\", $parts);
    }

    public function shortName()
    {
        $parts = explode("\\", $this->fullyQualifiedName);

        return array_pop($parts);
    }

    public function relativeName()
    {
        return $this->relativeName;
    }

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
        return "1373136332" . $this->fullyQualifiedName . $this->relativeName;
    }

    public function fullyQualified()
    {
        return new PhpName($this->fullyQualifiedName, $this->fullyQualifiedName);
    }
}
