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

use QafooLabs\Refactoring\Utils\ValueObject;

/**
 * Defined variables that are used or assigned.
 *
 * @property-read $localVariables
 * @property-read $assignments
 */
class DefinedVariables extends ValueObject
{
    /**
     * Name of variables that are "used" locally.
     *
     * @var array
     */
    protected $localVariables;

    /**
     * Name of variables that are assigned.
     *
     * @var array
     */
    protected $assignments;

    public function __construct(array $localVariables = array(), array $assignments = array())
    {
        $this->localVariables = $localVariables;
        $this->assignments = $assignments;
    }

    public function read()
    {
        return array_keys($this->localVariables);
    }

    public function changed()
    {
        return array_keys($this->assignments);
    }

    /**
     * Does list contain the given variable?
     *
     * @return bool
     */
    public function contains(Variable $variable)
    {
        return (
            isset($this->localVariables[$variable->getName()]) ||
            isset($this->assignments[$variable->getName()])
        );
    }

    public function getEndLine()
    {
        if (!$this->localVariables && !$this->assignments) {
            return 0;
        }

        return max(array_merge(array_map('max', $this->localVariables), array_map('max', $this->assignments)));
    }

    public function variablesFromSelectionUsedAfter(DefinedVariables $selection)
    {
        $selectionAssignments = $selection->changed();
        $endLine = $selection->getEndLine();
        $variablesUsedAfter = array();

        foreach ($selectionAssignments as $variable) {
            if ( ! isset($this->localVariables[$variable])) {
                continue;
            }

            $lastUsedLine = max($this->localVariables[$variable]);

            if ($lastUsedLine > $endLine) {
                $variablesUsedAfter[] = $variable;
            }
        }

        return $variablesUsedAfter;
    }
}
