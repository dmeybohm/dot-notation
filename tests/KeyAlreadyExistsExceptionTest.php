<?php

namespace Best\DotNotation\Test;

use Best\DotNotation\InconsistentKeyTypes;
use Best\DotNotation\KeyAlreadyExistsException;

class KeyAlreadyExistsExceptionTest extends \PHPUnit\Framework\TestCase
{
    public function testKeyAlreadyExistsExceptionCanBeCaughtWhenThrowingInconsistentKeyTypes()
    {
        try {
            throw new InconsistentKeyTypes('1', array(), 'foo');
        }
        catch (KeyAlreadyExistsException $e) {
            $caught = true;
        }
        $this->assertTrue(isset($caught));
    }

}
