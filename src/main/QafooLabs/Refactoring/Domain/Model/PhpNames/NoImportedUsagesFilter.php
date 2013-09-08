<?php

namespace QafooLabs\Refactoring\Domain\Model\PhpNames;

use QafooLabs\Refactoring\Domain\Model\PhpName;

/**
 * Filters a list of php occurances by all items expect the imported relative usages.
 */
class NoImportedUsagesFilter
{
    /**
     * @param array<PhpNameOccurance>
     *
     * @return array<PhpNameOccurance>
     */
    public function filter(array $phpNames)
    {
        $fileUseOccurances = array_flip(
            array_map(
                function ($useOccurance) {
                    return $useOccurance->name()->fullyQualifiedName();
                },
                array_filter(
                    $phpNames,
                    function ($occurance) {
                        return $occurance->name()->type() === PhpName::TYPE_USE;
                    }
                )
            )
        );

        return array_filter(
            $phpNames,
            function ($occurance) use ($fileUseOccurances) {
                return (
                    $occurance->name()->type() !== PhpName::TYPE_USAGE ||
                    !isset($fileUseOccurances[$occurance->name()->fullyQualifiedName()])
                );
            }
        );
    }
}
