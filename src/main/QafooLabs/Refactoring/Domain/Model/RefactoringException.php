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

    static public function rangeIsNotInsideMethod(LineRange $range)
    {
        return new self(sprintf(
            'The range %d-%d is not inside one single method.',
            $range->getStart(), $range->getEnd()
        ));
    }

    static public function rangeIsNotLocalScope(LineRange $range)
    {
        return new self(sprintf(
            'The range %d-%d is not inside a method or a function.',
            $range->getStart(), $range->getEnd()
        ));
    }
}
