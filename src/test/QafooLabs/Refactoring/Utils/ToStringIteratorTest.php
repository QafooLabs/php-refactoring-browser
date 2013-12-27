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

class ToStringIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertsObjectsToStrings()
    {
        $data = array(
            new StringableClass('value1'),
            new StringableClass('value2'),
        );

        $it = new ToStringIterator(new ArrayIterator($data));

        $this->assertEquals(
            array('value1', 'value2'),
            iterator_to_array($it)
        );
    }
}

class StringableClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }
}
