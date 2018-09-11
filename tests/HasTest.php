<?php

namespace Best\DotNotation\Test;

use Best\DotNotation;
use PHPUnit\Framework\TestCase;

class HasTest extends TestCase
{
    /**
     * @dataProvider provideHas
     */
    public function testHas($data, $keyPath, $expected)
    {
        $this->assertEquals($expected, DotNotation::has($data, $keyPath));
    }

    public function provideHas()
    {
        return array(
            'get empty array' => array(
                'data' => array('foo' => array()),
                'keyPath' => 'foo',
                'expected' => true
            ),
            'get empty array as second key' => array(
                'data' => array('foo' => array('bar' => array())),
                'keyPath' => 'foo.bar',
                'expected' => true
            ),
            'get one path' => array(
                'data' => array('foo' => array('baz' => 'cheese')),
                'keyPath' => 'foo.baz',
                'expected' => true
            ),
            'indexed array 1' => array(
                'data' => array('foo' => array('baz' => array('cheese', 'mozzarella'))),
                'keyPath' => 'foo.baz.0',
                'expected' => true
            ),
            'indexed array 2' => array(
                'data' => array('foo' => array('baz' => array('cheese', 'mozzarella'))),
                'keyPath' => 'foo.baz.1',
                'expected' => true
            ),
            'null returns true' => array(
                'data' => array('foo' => array('baz' => array(null, 'mozzarella'))),
                'keyPath' => 'foo.baz.0',
                'expected' => true
            ),
            'outside array returns false' => array(
                'data' => array('foo' => array('baz' => array(null, 'mozzarella'))),
                'keyPath' => 'foo.baz.2',
                'expected' => false
            ),
            'undefined key returns false' => array(
                'data' => array('foo' => array('baz' => array(null, 'mozzarella'))),
                'keyPath' => 'undefined.key.altogether',
                'expected' => false
            ),
        );
    }
}