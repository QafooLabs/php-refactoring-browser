<?php

namespace QafooLabs\Refactoring\Domain\Services;

use QafooLabs\Refactoring\Domain\Model\DefinedProperties;
use QafooLabs\Refactoring\Domain\Model\LineRange;
use QafooLabs\Refactoring\Domain\Model\File;
use QafooLabs\Refactoring\Domain\Model\DefinedVariables;

interface VariableScanner
{
    /**
     * Scan a line range within a file for defined variables.
     *
     * @return DefinedVariables
     */
    public function scanForVariables(File $file, LineRange $range);

    /**
     * Scan a line range within a file for properties.
     *
     * @return DefinedProperties
     */
    public function scanForProperties(File $file, LineRange $range);
}
