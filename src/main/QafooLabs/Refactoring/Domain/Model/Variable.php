<?php

namespace QafooLabs\Refactoring\Domain\Model;

/**
 * Represent a variable in the refactoring domain.
 */
class Variable
{
    private $name;

    public function __construct($name)
    {
        if (preg_match('(([\s;\(\)]+))', $name)) {
            throw RefactoringException::illegalVariableName($name);
        }

        $this->name = ltrim($name, '$');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return '$' . $this->name;
    }

    /**
     * @return bool
     */
    public function isLocal()
    {
        return ! $this->isInstance();
    }

    /**
     * @return bool
     */
    public function isInstance()
    {
        return strpos($this->name, 'this->') === 0;
    }

    /**
     * Create a new variable of the local variable that is an instance variable.
     */
    public function convertToInstance()
    {
        if ( ! $this->isLocal()) {
            throw RefactoringException::variableNotLocal($this);
        }

        return new Variable('$this->' . $this->name);
    }
}

