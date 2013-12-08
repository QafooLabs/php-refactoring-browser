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

    public function __construct(EditorBuffer $buffer)
    {
        $this->buffer = $buffer;
    }

    public function replaceString(DefinedVariables $definedVariables, Variable $oldName, Variable $newName)
    {
        $this->replaceStringInArray($definedVariables->all(), $oldName, $newName);
    }

    private function replaceStringInArray(array $variables, Variable $oldName, Variable $newName)
    {
        if (isset($variables[$oldName->getName()])) {
            foreach ($variables[$oldName->getName()] as $line) {
                $this->buffer->replaceString($line, $oldName->getToken(), $newName->getToken());
            }
        }
    }

    public function replaceRangeWithMethodCall(LineRange $range, MethodSignature $newMethod)
    {
        $argumentLine = $this->implodeVariables($newMethod->arguments());

        $code = $newMethod->isStatic() ? 'self::%s(%s);' : '$this->%s(%s);';
        $call = sprintf($code, $newMethod->getName(), $argumentLine);

        if (count($newMethod->returnVariables()) == 1) {
            $call = '$' . $newMethod->returnVariables()[0] . ' = ' . $call;
        } else if (count($newMethod->returnVariables()) > 1) {
            $call = 'list(' . $this->implodeVariables($newMethod->returnVariables()) . ') = ' . $call;
        }

        $lines = $this->buffer->getLines($range);
        $indent = $this->leftWhitespacesOf(reset($lines));

        $this->buffer->replace($range, array($this->whitespace($indent) . $call));
    }

    public function addMethod($line, MethodSignature $newMethod, $selectedCode)
    {
        if (count($newMethod->returnVariables()) == 1) {
            $selectedCode[] = '';
            $selectedCode[] = $this->whitespace(8) . 'return $' . $newMethod->returnVariables()[0] . ';';
        } else if (count($newMethod->returnVariables()) > 1) {
            $selectedCode[] = '';
            $selectedCode[] = $this->whitespace(8) . 'return array(' . $this->implodeVariables($newMethod->returnVariables()) . ');';
        }

        $methodCode = array_merge(
            array(
                '',
                $this->whitespace(4) . $this->renderMethodSignature($newMethod),
                $this->whitespace(4) . '{'
            ),
            $this->realign($selectedCode, 8),
            array($this->whitespace(4) . '}')
        );

        $this->buffer->append($line, $methodCode);
    }

    private function alignedAtWhitespaces(array $lines)
    {
        return array_reduce($lines, function ($minWhitespace, $line) {
            if ($this->isEmptyLine($line)) {
                return $minWhitespace;
            }

            return min($minWhitespace, $this->leftWhitespacesOf($line));
        }, 100);
    }

    private function realign(array $lines, $atWhitespaces)
    {
        $minWhitespaces = $this->alignedAtWhitespaces($lines);
        $whitespaceCorrection = $atWhitespaces - $minWhitespaces;

        if ($whitespaceCorrection === 0) {
            return $lines;
        }

        return array_map(function ($line) use($whitespaceCorrection) {
            if ($whitespaceCorrection > 0) {
                return $this->whitespace($whitespaceCorrection) . $line;
            }

            // Should we verify that the characters removed here are actually whitespace?
            return substr($line, abs($whitespaceCorrection));
        }, $lines);
    }

    private function isEmptyLine($line)
    {
        return trim($line) === "";
    }

    private function leftWhitespacesOf($line)
    {
        return strlen($line) - strlen(ltrim($line));
    }

    private function renderMethodSignature(MethodSignature $method)
    {
        $paramLine = $this->implodeVariables($method->arguments());

        return sprintf('private%sfunction %s(%s)', $method->isStatic() ? ' static ' : ' ', $method->getName(), $paramLine);
    }

    private function implodeVariables($variableNames)
    {
        return implode(', ', array_map(function ($variableName) {
            return '$' . $variableName;
        }, $variableNames));
    }

    public function addProperty($line, $propertyName)
    {
        $this->buffer->append($line, array(
            $this->whitespace(4) . 'private $' . $propertyName . ';', ''
        ));
    }

    private function whitespace($number)
    {
        return str_repeat(' ', $number);
    }
}
