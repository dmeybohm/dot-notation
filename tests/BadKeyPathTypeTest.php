<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\BadKey;

class BadKeyPathTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNewInstance()
    {
        $this->assertNotNull(new BadKey('hello'));
    }

    /**
     * @expectedException \Best\DotNotation\BadKey
     * @expectedExceptionMessageRegExp /Variable is not a string or int.*true/
     */
    public function testThrowingExceptionContainsDefaultMessage()
    {
        throw new BadKey(true);
    }

}
