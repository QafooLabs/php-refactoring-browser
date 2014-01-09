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

class EditingSession
{
    /**
     * @var EditorBuffer
     */
    private $buffer;

    /**
     * @var EditingAction[]
     */
    private $actions = array();

    public function __construct(EditorBuffer $buffer)
    {
        $this->buffer = $buffer;
    }

    public function addEdit(EditingAction $action)
    {
        $this->actions[] = $action;
    }

    public function performEdits()
    {
        foreach ($this->actions as $action) {
            $action->performEdit($this->buffer);
        }
    }
}
