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
use Closure;

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

    public function all()
    {
        $all = $this->localVariables;

        foreach ($this->assignments as $name => $lines) {
            if ( ! isset($all[$name])) {
                $all[$name] = array();
            }

            $all[$name] = array_unique(array_merge($all[$name], $lines));

            sort($all[$name]);
        }

        return $all;
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

    public function getStartLine()
    {
        if (!$this->localVariables && !$this->assignments) {
            return 0;
        }

        return min(array_merge(array_map('min', $this->localVariables), array_map('min', $this->assignments)));
    }

    public function variablesFromSelectionUsedAfter(DefinedVariables $selection)
    {
        $selectionAssignments = $selection->changed();
        return $this->filterVariablesFromSelection($selectionAssignments, $selection, function ($lastUsedLine, $endLine) {
            return $lastUsedLine > $endLine;
        }, 'max');
    }

    public function variablesFromSelectionUsedBefore(DefinedVariables $selection)
    {
        return $this->filterVariablesFromSelection($selection->read(), $selection, function ($lastUsedLine, $endLine) {
            return $lastUsedLine < $endLine;
        }, 'min');
    }

    private function filterVariablesFromSelection($selectedVariables, DefinedVariables $selection, Closure $filter, $fn)
    {
        $variablesUsed = array();

        $compareLine = $fn == 'max'
            ? $selection->getEndLine()
            : $selection->getStartLine();
        $knownVariables = $this->all();

        foreach ($selectedVariables as $variable) {
            if ( ! isset($knownVariables[$variable])) {
                continue;
            }

            $lastUsedLine = $fn($knownVariables[$variable]);

            if ($filter($lastUsedLine, $compareLine)) {
                $variablesUsed[] = $variable;
            }
        }

        return $variablesUsed;
    }
}
