<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\InconsistentKeyTypes;

class InconsistentKeyTypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that KeyAlreadyExists can be instantiated.
     *
     * @return void
     */
    public function testKeyAlreadyExistsCanBeInstantiated()
    {
        $this->assertNotNull(new InconsistentKeyTypes('foo',  array('bar'), 'foo.bar'));
    }

    /**
     * @expectedException \Best\DotNotation\InconsistentKeyTypes
     * @expectedExceptionMessageRegExp /Attempting to change key 'foo.bar' from a non-array to an array/
     */
    public function testDefaultErrorMessage()
    {
        throw new InconsistentKeyTypes('bar', array('baz'), 'foo.bar');
    }
}
