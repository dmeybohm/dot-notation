<?php

namespace Best\Test;

use Best\DotNotation\KeyAlreadyExistsException;

class KeyAlreadyExistsExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that KeyAlreadyExistsException can be instantiated.
     *
     * @return void
     */
    public function testKeyAlreadyExistsExceptionCanBeInstantiated()
    {
        $this->assertNotNull(new KeyAlreadyExistsException());
    }
}
