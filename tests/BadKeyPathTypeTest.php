<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\BadKeyPathType;

class BadKeyPathTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNewInstance()
    {
        $this->assertNotNull(new BadKeyPathType('hello'));
    }

    /**
     * @expectedException \Best\DotNotation\BadKeyPathType
     * @expectedExceptionMessageRegExp /Variable is not a string or int.*true/
     */
    public function testThrowingExceptionContainsDefaultMessage()
    {
        throw new BadKeyPathType(true);
    }

}
