<?php

namespace QafooLabs\Collections;

class SetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function whenAddingItemMultipleTimes_ThenOnlyAddItOnce()
    {
        $item = 'A';

        $set = new Set();
        $set->add($item);
        $set->add($item);

        $this->assertEquals(1, count($set));
    }

    /**
     * @test
     */
    public function whenAddingMultipleItems_ThenCountThemUniquely()
    {
        $item1 = 'A';
        $item2 = 'B';

        $set = new Set();
        $set->add($item1);
        $set->add($item1);
        $set->add($item2);

        $this->assertEquals(2, count($set));
    }

    /**
     * @test
     */
    public function whenAddingHashableObjectMultipleTimes_ThenOnlyAddItOnce()
    {
        $item1 = new FooObject(1);
        $item2 = new FooObject(2);

        $set = new Set();
        $set->add($item1);
        $set->add($item1);
        $set->add($item2);
        $set->add($item2);

        $this->assertEquals(2, count($set));
    }

    /**
     * @test
     */
    public function whenIteratingOverSet_ThenReturnAllUniqueItems()
    {
        $item1 = 'A';
        $item2 = 'B';

        $set = new Set();
        $set->add($item1);
        $set->add($item2);

        $values = array();

        foreach ($set as $key => $value) {
            $values[$key] = $value;
        }

        $this->assertEquals(array(0 => 'A', 1 => 'B'), $values);
    }
}

class FooObject implements Hashable
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function hashCode()
    {
        return md5($this->value);
    }
}
