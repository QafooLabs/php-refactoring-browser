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

class CallbackFilterIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterEmptyElements()
    {
        $values = new CallbackFilterIterator(
            new ArrayIterator(array(1, null, false, "", 2)),
            function ($value) {
                return !empty($value);
            }
        );

        $this->assertEquals(array(1, 2), array_values(iterator_to_array($values)));
    }
}
