<?php

namespace QafooLabs\Refactoring\Domain\Model;

use Exception;

class RefactoringException extends Exception
{
    static public function illegalVariableName($name)
    {
        return new self(sprintf('The given variable name "%s" is not valid in PHP.', $name));
    }

    static public function variableNotInRange(Variable $variable, LineRange $range)
    {
        return new self(sprintf('Could not find variable "%s" in line range %d-%d.',
            $variable->getToken(), $range->getStart(), $range->getEnd()
        ));
    }

    static public function variableNotLocal(Variable $variable)
    {
        return new self(sprintf(
            'Given variable "%s" is required to be local to the current method.',
            $variable->getToken()
        ));
    }
}
