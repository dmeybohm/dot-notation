<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\KeyNotFound;

class KeyNotFoundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that KeyNotFound can be instantiated.
     *
     * @return void
     */
    public function testKeyNotFoundCanBeInstantiated()
    {
        $this->assertNotNull(new KeyNotFound('foo.bar'));
    }

    /**
     * @expectedException \Best\DotNotation\KeyNotFound
     * @expectedExceptionMessageRegExp /Key path 'foo.bar' not found/
     */
    public function testKeyNotFoundHasDefaultMessage()
    {
        throw new KeyNotFound('foo.bar');
    }

}
