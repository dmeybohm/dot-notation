<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\InvalidKeyPath;

class InvalidKeyPathTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNewInstance()
    {
        $this->assertNotNull(new InvalidKeyPath('hello'));
    }

    /**
     * @expectedException \Best\DotNotation\InvalidKeyPath
     * @expectedExceptionMessageRegExp /Variable is not a string or int.*true/
     */
    public function testThrowingExceptionContainsDefaultMessage()
    {
        throw new InvalidKeyPath(true);
    }

}
