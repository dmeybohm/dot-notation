<?php

namespace Best\DotNotation\Test;

use Best\DotNotation;

class SetGetAndRemoveTest extends \PHPUnit\Framework\TestCase
{
    public function testGet()
    {
        $arrayOne = array('foo' => array('baz' => 'cheese'));
        $this->assertEquals('cheese', DotNotation::get($arrayOne, 'foo.baz'));
    }

    /**
     * @dataProvider provideInvalidKeyPath
     * @expectedException \InvalidArgumentException
     */
    public function testGetThrowsInvalidArgumentExceptionForInvalidKeyPaths($keyPath)
    {
        $arrayOne = array('foo' => array('baz' => 'cheese'));
        $this->assertEquals('cheese', DotNotation::get($arrayOne, $keyPath));
    }

    public function testGetCanHandleIndexedArrays()
    {
        $arrayOne = array('foo' => array('baz' => array('cheese', 'mozzarella')));
        $this->assertEquals('mozzarella', DotNotation::get($arrayOne, 'foo.baz.1'));
    }

    /**
     * @expectedException \Best\DotNotation\KeyNotFound
     */
    public function testGetThrowsKeyNotFoundIfKeyIsNotFound()
    {
        $arrayOne = array('foo' => array('baz' => 'cheese'));
        $this->assertEquals('cheese', DotNotation::get($arrayOne, 'foo.undefined'));
    }

    public function testGetOrDefaultReturnsDefaultValueIfKeyIsNotFound()
    {
        $arrayOne = array('foo' => array('baz' => 'cheese'));
        $this->assertEquals('default value',
            DotNotation::getOrDefault($arrayOne, 'foo.undefined', 'default value'));
    }

    public function testSet()
    {
        $arrayIntoArray = array('foo' => array('bar' => 'cheese'));
        $arrayExpected = array('foo' => array('bar' => array('into array')));
        $this->assertEquals($arrayExpected, DotNotation::set($arrayIntoArray, 'foo.bar', array('into array')));

        $arrayIntoNull = array('foo' => array('bar' => 'cheese'));
        $nullExpected = array('foo' => array('bar' => null));
        $this->assertEquals($nullExpected, DotNotation::set($arrayIntoNull, 'foo.bar', null));
    }

    /**
     * @dataProvider provideInvalidKeyPath
     * @expectedException \InvalidArgumentException
     */
    public function testSetThrowsInvalidArgumentExceptionIfKeyPathIsNotAnIntegerOrString($keyPath)
    {
        $arrayIntoArray = array('foo' => array('bar' => 'cheese'));
        $arrayExpected = array('foo' => array('bar' => array('into array')));
        $this->assertEquals($arrayExpected, DotNotation::set($arrayIntoArray, $keyPath, array('into array')));
    }

    /**
     * @expectedException \Best\DotNotation\KeyAlreadyExists
     */
    public function testSetThrowsKeyAlreadyExistsIfSubKeyIsNotAnArray()
    {
        $array = array('foo' => array('non_array' => true, 'bar' => 'cheese'));
        DotNotation::set($array, 'foo.non_array.cheese', array('into array'));
    }

    public function testSetCanTruncateArrayValuesWithoutException()
    {
        $array = array('foo' => array('array' => array('something else' => 'into another')));
        $expected = array('foo' => array('array' => true));
        $this->assertEquals($expected, DotNotation::set($array, 'foo.array', true));
    }

    public function testRemove()
    {
        $array = array('foo' => array('array' => array('something else' => 'into another')));
        $this->assertEquals(array('foo' => array()), DotNotation::remove($array, 'foo.array'));

        $indexedArray = array('foo' => array('array' => array('something else', 'into another')));
        $this->assertEquals(array('foo' => array('array' => array('something else'))),
            DotNotation::remove($indexedArray, 'foo.array.1'));
        $this->assertEquals(array('foo' => array('array' => array(1 => 'into another'))),
            DotNotation::remove($indexedArray, 'foo.array.0'));
    }

    /**
     * @expectedException \Best\DotNotation\KeyNotFound
     */
    public function testRemoveThrowsKeyNotFoundIfValueIsAttemptedToBeRemoved()
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
        DotNotation::removeIfExists($array, 'undefined');
    }

    public function testRemoveIfExistsDoesNotThrowIfKeyDoesNotExist()
    {
        $array = array('foo' => array('array' => array('something else' => 'into another')));
        $this->assertEquals($array, DotNotation::removeIfExists($array, 'undefined'));
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
