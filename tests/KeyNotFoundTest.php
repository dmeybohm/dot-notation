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

    public function testKeyNotFoundHasDefaultMessage()
    {
        $this->expectException(\Best\DotNotation\KeyNotFound::class);
        $this->expectExceptionMessageMatches('/Key path \'foo.bar\' not found/');
        throw new KeyNotFound('foo.bar');
    }

}
