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

class PhpName
{

    private $fullyQualifiedName;
    private $relativeName;
    private $file;
    private $declaredLine;
    private $parent;

    public function __construct($fullyQualifiedName, $relativeName, File $file = null, $declaredLine = null, $parent = null)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        $this->relativeName = $relativeName;
        $this->file = $file;
        $this->declaredLine = $declaredLine;
        $this->parent = $parent;
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
        $otherParts = explode("\\", $other->relativeName);
        $thisParts = explode("\\", $this->relativeName);

        $prefix = array();
        foreach ($otherParts as $otherPart) {
            if ($otherPart === $thisParts[0]) {
                return ltrim(implode("\\", $prefix) . "\\" . $this->relativeName, '\\') === $this->fullyQualifiedName;
            }

            $prefix[] = $otherPart;
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
        return new PhpName(implode('\\', $newParts), implode('\\', $relativeNewParts), $this->file, $this->declaredLine);
    }

    public function file()
    {
        return $this->file;
    }

    public function declaredLine()
    {
        return $this->declaredLine;
    }

    public function fullyQualifiedName()
    {
        return $this->fullyQualifiedName;
    }

    public function relativeName()
    {
        return $this->relativeName;
    }

    public function parent() {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function equals(PhpName $other)
    {
        return $this->fullyQualifiedName === $other->fullyQualifiedName &&
               $this->relativeName === $other->relativeName;
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
        return $this->parent instanceof UseStatement;
    }

    /**
     * @return string
     */
    public function shortName()
    {
        $parts = explode("\\", $this->fullyQualifiedName);
        return end($parts);
    }
}
