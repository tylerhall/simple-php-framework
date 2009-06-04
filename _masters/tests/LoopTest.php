<?php
require_once 'PHPUnit/Framework.php';
require_once 'includes/class.loop.php';
 
class LoopTest extends PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $l = new Loop('foo', 'bar', 'charlie');
        $this->assertEquals($l->get(), 'foo');
        $this->assertEquals($l->get(), 'bar');
        $this->assertEquals($l->get(), 'charlie');
        $this->assertEquals($l->get(), 'foo');
    }

    public function testToString()
    {
        $l = new Loop('foo', 'bar', 'charlie');
        $this->assertEquals($l->__tostring(), 'foo');
        $this->assertEquals($l->__tostring(), 'bar');
        $this->assertEquals($l->__tostring(), 'charlie');
        $this->assertEquals($l->__tostring(), 'foo');
    }

    public function testRandom()
    {
        $l = new Loop('foo', 'bar', 'charlie');
        $arr = array('foo', 'bar', 'charlie');
        $this->assertTrue(in_array($l->rand(), $arr));
        $this->assertTrue(in_array($l->rand(), $arr));
        $this->assertTrue(in_array($l->rand(), $arr));
        $this->assertTrue(in_array($l->rand(), $arr));
    }
}
