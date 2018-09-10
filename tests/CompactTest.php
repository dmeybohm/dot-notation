<?php

namespace Best\DotNotation\Test;

use Best\DotNotation;
use PHPUnit\Framework\TestCase;

class CompactTest extends TestCase
{
    /**
     * Tests that dots are compacted.
     *
     * @return void
     */
    public function testDotsAreCompacted()
    {
        $array = DotNotation::compact(array(
            'foo' => array(
                'bar' => array(
                    'blah' => 'buzz',
                    'subkey1' => array('subkey2' => 'subvalue'),
                ),
            ),
        ));
        $expect = array(
            'foo.bar' => array(
                'blah' => 'buzz',
                'subkey1.subkey2' => 'subvalue'
            )
        );
        $this->assertEquals($expect, $array);
    }

    /**
     * Tests that dots are compacted correctly with non-indexed arrays.
     *
     * @return void
     */
    public function testDotsAreCompactedCorrectlyWithNonIndexedArrays()
    {
        $array = DotNotation::compact(array(
            'foo' => array(
                'bar' => array(
                    'buzz',
                    'baz' => array('foo'),
                ),
            ),
            'key' => array(
                'subkey1' => array(
                    'subkey2' => array(
                        array('value')
                    )
                )
            )
        ));
        $expect = array(
            'foo.bar' => array(
                'buzz',
                'baz' => array('foo')
            ),
            'key.subkey1.subkey2' => array(
                array('value')
            )
        );
        $this->assertEquals($expect, $array);
    }

    /**
     * Tests that dots are escaped when compacting.
     *
     * @return void
     */
    public function testDotsAreEscapedWhenCompacting()
    {
        $array = DotNotation::compact(array(
            'foo.bar' => array(
                'bar' => array(
                    'buzz',
                    'baz' => array('foo'),
                ),
            ),
            'key' => array(
                'subkey1' => array(
                    'subkey2.dotted' => array(
                        array('value')
                    )
                )
            )
        ));
        $expect = array(
            'foo\.bar.bar' => array(
                'buzz',
                'baz' => array('foo')
            ),
            'key.subkey1.subkey2\.dotted' => array(
                array('value')
            )
        );
        $this->assertEquals($expect, $array);
    }

}