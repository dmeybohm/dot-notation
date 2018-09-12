<?php

namespace Best\DotNotation\Test;

use Best\DotNotation;

class SetGetAndRemoveTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideGet
     */
    public function testGet($data, $keyPath, $expected)
    {
        $this->assertEquals($expected, DotNotation::get($data, $keyPath));
    }

    public function provideGet()
    {
        return array(
            'get empty array' => array(
                'data' => array('foo' => array()),
                'keyPath' => 'foo',
                'expected' => array()
            ),
            'get empty array as second key' => array(
                'data' => array('foo' => array('bar' => array())),
                'keyPath' => 'foo.bar',
                'expected' => array()
            ),
            'get one path' => array(
                'data' => array('foo' => array('baz' => 'cheese')),
                'keyPath' => 'foo.baz',
                'expected' => 'cheese'
            ),
            'indexed array 1' => array(
                'data' => array('foo' => array('baz' => array('cheese', 'mozzarella'))),
                'keyPath' => 'foo.baz.0',
                'expected' => 'cheese'
            ),
            'indexed array 2' => array(
                'data' => array('foo' => array('baz' => array('cheese', 'mozzarella'))),
                'keyPath' => 'foo.baz.1',
                'expected' => 'mozzarella'
            ),
        );
    }

    /**
     * @dataProvider provideInvalidKeyPath
     * @expectedException \Best\DotNotation\BadKeyPath
     */
    public function testGetThrowsBadKeyPath($keyPath)
    {
        $arrayOne = array('foo' => array('baz' => 'cheese'));
        $this->assertEquals('cheese', DotNotation::get($arrayOne, $keyPath));
    }

    /**
     * @expectedException \Best\DotNotation\KeyNotFound
     */
    public function testGetThrowsKeyNotFound()
    {
        $arrayOne = array('foo' => array('baz' => 'cheese'));
        $this->assertEquals('cheese', DotNotation::get($arrayOne, 'foo.undefined'));
    }

    /**
     * @dataProvider provideGet
     */
    public function testGetOrDefaultImitatesGet($data, $keyPath, $expected)
    {
        $this->assertEquals($expected, DotNotation::getOrDefault($data, $keyPath, null));
    }

    /**
     * @dataProvider provideGetOrDefault
     */
    public function testGetOrDefault($data, $keyPath, $expected)
    {
        $this->assertEquals($expected, DotNotation::getOrDefault($data, $keyPath, 'default'));
    }

    public function provideGetOrDefault()
    {
        return array(
            'second level key' => array(
                'data' => array('foo' => array('baz' => 'cheese')),
                'keyPath' => 'foo.undefined',
                'expected' => 'default'
            ),
            'first level key' => array(
                'data' => array('foo' => array('baz' => 'cheese')),
                'keyPath' => 'bar',
                'expected' => 'default'
            ),
            'get value' => array(
                'data' => array('foo' => array('baz' => 'cheese')),
                'keyPath' => 'foo.baz',
                'expected' => 'cheese'
            ),
        );
    }

    /**
     * @dataProvider provideGetOrDefault
     */
    public function testGetOrNull($data, $keyPath, $expected)
    {
        $expected = $expected === 'default' ? null : $expected;
        $this->assertEquals($expected, DotNotation::getOrNull($data, $keyPath));
    }

    /**
     * @dataProvider provideSet
     */
    public function testSet($data, $keyPath, $value, $expected)
    {
        DotNotation::set($data, $keyPath, $value);
        $this->assertEquals($expected, $data);
    }

    public function provideSet()
    {
        return array(
            'override array' => array(
                'data' => array('foo' => array('bar' => 'cheese')),
                'keyPath' => 'foo.bar',
                'value' => array('into array'),
                'expected' => array('foo' => array('bar' => array('into array'))),
            ),
            'set null' => array(
                'data' => array('foo' => array('bar' => 'cheese')),
                'keyPath' => 'foo.bar',
                'value' => null,
                'expected' => array('foo' => array('bar' => null)),
            ),
            'inside empty array' => array(
                'data' => array(),
                'keyPath' => 'foo',
                'value' => 'cheese',
                'expected' => array('foo' => 'cheese'),
            ),
            'inside empty array nested' => array(
                'data' => array(),
                'keyPath' => 'foo.bar',
                'value' => 'cheese',
                'expected' => array('foo' => array('bar' => 'cheese')),
            ),
            'truncate array values without exception' => array(
                'data' => array(),
                'keyPath' => 'foo.bar',
                'value' => 'cheese',
                'expected' => array('foo' => array('bar' => 'cheese')),
            ),
        );
    }

    /**
     * @dataProvider provideInvalidKeyPath
     * @expectedException \Best\DotNotation\BadKeyPath
     */
    public function testSetThrowsBadKeyPathIfKeyPathIsNotAnIntegerOrString($keyPath)
    {
        $arrayIntoArray = array('foo' => array('bar' => 'cheese'));
        DotNotation::set($arrayIntoArray, $keyPath, array('into array'));
    }

    /**
     * @expectedException \Best\DotNotation\InconsistentKeyTypes
     */
    public function testSetThrowsInconsistentKeyTypesIfNonArrayKeyIsSet()
    {
        $data = array('foo' => array('bar' => true));
        DotNotation::set($data, 'foo.bar.cheese', 'mozzarella');
    }

    /**
     * @expectedException \Best\DotNotation\InconsistentKeyTypes
     */
    public function testSetThrowsKeyAlreadyExistsIfSubKeyIsNotAnArray()
    {
        $array = array('foo' => array('non_array' => true, 'bar' => 'cheese'));
        DotNotation::set($array, 'foo.non_array.cheese', array('into array'));
    }

    /**
     * @dataProvider provideSet
     */
    public function testSetAndOverridePassesSetTest($data, $keyPath, $value, $expected)
    {
        DotNotation::setAndOverride($data, $keyPath, $value);
        $this->assertEquals($expected, $data);
    }

    /**
     * @dataProvider provideSetAndOverride
     */
    public function testSetAndOverride($data, $keyPath, $value, $expected)
    {
        DotNotation::setAndOverride($data, $keyPath, $value);
        $this->assertEquals($expected, $data);
    }

    public function provideSetAndOverride()
    {
        return array(
            'inside empty array' => array(
                'data' => array(),
                'keyPath' => 'foo.bar',
                'value' => 'cheese',
                'expected' => array('foo' => array('bar' => 'cheese'))
            ),
            'override non-array keys' => array(
                'data' => array('foo' => array('bar' => true)),
                'keyPath' => 'foo.bar.cheese',
                'value' => 'mozzarella',
                'expected' => array('foo' => array('bar' => array('cheese' => 'mozzarella')))
            ),
        );
    }

    /**
     * @dataProvider provideRemove
     */
    public function testRemove($data, $keyPath, $expected)
    {
        DotNotation::remove($data, $keyPath);
        $this->assertEquals($expected, $data);
    }

    public function provideRemove()
    {
        return array(
            'remove child key' => array(
                'data' => array('foo' => array('array' => array('something else' => 'into another'))),
                'keyPath' => 'foo.array',
                'expected' => array('foo' => array())
            ),
            'remove top key' => array(
                'data' => array('foo' => array('array' => array('something else' => 'into another'))),
                'keyPath' => 'foo',
                'expected' => array()
            ),
            'indexed key 1' => array(
                'data' => array('foo' => array('array' => array('something else', 'into another'))),
                'keyPath' => 'foo.array.0',
                'expected' => array('foo' => array('array' => array(1 => 'into another')))
            ),
            'indexed key 2' => array(
                'data' => array('foo' => array('array' => array('something else', 'into another'))),
                'keyPath' => 'foo.array.1',
                'expected' => array('foo' => array('array' => array('something else')))
            ),
        );
    }

    /**
     * @expectedException \Best\DotNotation\KeyNotFound
     */
    public function testRemoveThrowsKeyNotFoundIfMissingValueIsAttemptedToBeRemoved()
    {
        $array = array('foo' => array('array' => array('something else' => 'into another')));
        DotNotation::remove($array, 'foo.array.0.something else');
    }

    /**
     * @expectedException \Best\DotNotation\KeyNotFound
     */
    public function testRemoveThrowsKeyNotFoundIfKeyDoesNotExist()
    {
        $array = array('foo' => array('array' => array('something else' => 'into another')));
        DotNotation::remove($array, 'undefined');
    }

    /**
     * @dataProvider provideRemove
     */
    public function testRemoveIfExistsImitatesRemove($data, $keyPath, $expected)
    {
        DotNotation::removeIfExists($data, $keyPath);
        $this->assertEquals($expected, $data);
    }

    /**
     * @dataProvider provideRemoveIfExists
     */
    public function testRemoveIfExists($data, $keyPath, $expected)
    {
        DotNotation::removeIfExists($data, $keyPath);
        $this->assertEquals($expected, $data);
    }

    public function provideRemoveIfExists()
    {
        return array(
            'undefined parent key' => array(
                'data' => array('foo' => array('array' => array('something else' => 'into another'))),
                'keyPath' => 'undefined',
                'expected' => array('foo' => array('array' => array('something else' => 'into another')))
            ),
            'undefined child key' => array(
                'data' => array('foo' => array('array' => array('something else' => 'into another'))),
                'keyPath' => 'foo.array.something else.missing key',
                'expected' => array('foo' => array('array' => array('something else' => 'into another')))
            ),
            'child key with space' => array(
                'data' => array('foo' => array('array' => array('something else' => 'into another'))),
                'keyPath' => 'foo.array.something else',
                'expected' => array('foo' => array('array' => array()))
            ),
        );
    }

    public function provideInvalidKeyPath()
    {
        return array(
            'null' => array(null),
            'float' => array(3.14),
            'bool' => array(true),
        );
    }
}
