<?php

namespace Dmeybohm\Test;

use Dmeybohm\DotNotation\KeyAlreadyExistsException;

class KeyAlreadyExistsExceptionTest extends \PHPUnit_Framework_TestCase
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