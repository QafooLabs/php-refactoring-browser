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

namespace QafooLabs\Refactoring\Utils;

use ArrayIterator;

class TransformIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformValues()
    {
        $strings = new ReverseStringTransformIterator(new ArrayIterator(array('Hello', 'World')));

        $this->assertEquals(array('olleH', 'dlroW'), iterator_to_array($strings));
    }
}

class ReverseStringTransformIterator extends TransformIterator
{
    protected function transform($value)
    {
        return strrev($value);
    }
}
