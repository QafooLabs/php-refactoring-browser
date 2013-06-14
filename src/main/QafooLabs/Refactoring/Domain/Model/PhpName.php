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
        $otherParts = explode("\\", $other->relativeName);
        $thisParts = explode("\\", $this->relativeName);

        $prefix = array();
        foreach ($otherParts as $otherPart) {
            if ($otherPart === $thisParts[0]) {
                return implode("\\", $prefix) . "\\" . $this->relativeName === $this->fullyQualifiedName;
            }

            $prefix[] = $otherPart;
        }

        return false;
    }

    public function isFullyQualified()
    {
        return $this->fullyQualifiedName === $this->relativeName;
    }
}
