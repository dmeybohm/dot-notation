<?php

namespace ZfConfigTest;

use ZfConfig\ZfConfig;

class ZfConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDotsAreExpandedToAppropriateArrayKeys()
    {
        $config = new ZfConfig(array('foo'         => array('bar' => 'buzz'),
                                     'foo.bar.baz' => array('foo')));
        $expect = array('foo' => array('bar' => array('buzz', 'baz' => array('foo'))));
        $this->assertEquals($expect, $config->toArray());
    }

}