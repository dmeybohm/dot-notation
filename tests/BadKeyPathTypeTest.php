<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\BadKeyPath;

class BadKeyPathTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateNewInstance()
    {
        $this->assertNotNull(new BadKeyPath('hello'));
    }

    public function testThrowingExceptionContainsDefaultMessage()
    {
        $this->expectException(\Best\DotNotation\BadKeyPath::class);
        $this->expectExceptionMessageMatches('/Variable is not a string or int.*true/');
        throw new BadKeyPath(true);
    }

}
