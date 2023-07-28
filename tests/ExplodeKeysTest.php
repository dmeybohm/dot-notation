<?php

namespace Best\DotNotation\Test;

class ExplodeKeysTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \ReflectionClass
     */
    private $class;

    /**
     * @var \ReflectionMethod
     */
    private $method;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->class = new \ReflectionClass('\Best\DotNotation');
        }
        catch (\ReflectionException $e) {
            throw new \RuntimeException();
        }

        $this->method = $this->class->getMethod('explodeKeys');
        $this->method->setAccessible(true);
    }

    /**
     * @dataProvider provideExplodeKeys
     */
    public function testExplodeKeys($keyPath, $expected)
    {
        $this->assertEquals($expected, $this->method->invoke(null, $keyPath));
    }

    public function provideExplodeKeys()
    {
        return array(
            'simple' => array(
                'keyPath' => 'foo.bar.baz',
                'expected' => array('foo', 'bar', 'baz')
            ),
            'with backslashes 1' => array(
                'keyPath' => 'foo.bar\.baz',
                'expected' => array('foo', 'bar.baz')
            ),
            'all backslashes' => array(
                'keyPath' => 'foo\.bar\.baz',
                'expected' => array('foo.bar.baz')
            ),
            'double backslashes' => array(
                'keyPath' => 'foo\\\\.bar\\\\.baz',
                'expected' => array('foo\\.bar\\.baz')
            ),
            'no escaped dots' => array(
                'keyPath' => '\Foo\Bar\Baz',
                'expected' => array('\Foo\Bar\Baz')
            ),
        );
    }

}
