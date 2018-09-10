<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\KeyAlreadyExists;

class KeyAlreadyExistsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that KeyAlreadyExists can be instantiated.
     *
     * @return void
     */
    public function testKeyAlreadyExistsCanBeInstantiated()
    {
        $this->assertNotNull(new KeyAlreadyExists('foo', 'foo.bar'));
    }

    /**
     * @expectedException \Best\DotNotation\KeyAlreadyExists
     * @expectedExceptionMessageRegExp /Attempting to change key 'foo.bar' from a non-array to an array/
     */
    public function testDefaultErrorMessage()
    {
        throw new KeyAlreadyExists('bar', 'foo.bar');
    }
}
