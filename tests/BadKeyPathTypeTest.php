<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\BadKeyPath;

class BadKeyPathTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNewInstance()
    {
        $this->assertNotNull(new BadKeyPath('hello'));
    }

    /**
     * @expectedException \Best\DotNotation\BadKeyPath
     * @expectedExceptionMessageRegExp /Variable is not a string or int.*true/
     */
    public function testThrowingExceptionContainsDefaultMessage()
    {
        throw new BadKeyPath(true);
    }

}
