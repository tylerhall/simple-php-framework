<?PHP
    require_once 'class.test.php';
    require_once '../../includes/class.loop.php';

    class TestLoop extends Test
    {
        function testGet()
        {
            $l = new Loop('foo', 'bar', 'charlie');
            $this->equals($l->get(), 'foo');
            $this->equals($l->get(), 'bar');
            $this->equals($l->get(), 'charlie');
            $this->equals($l->get(), 'foo');
        }

        function testToString()
        {
            $l = new Loop('foo', 'bar', 'charlie');
            $this->equals($l->__tostring(), 'foo');
            $this->equals($l->__tostring(), 'bar');
            $this->equals($l->__tostring(), 'charlie');
            $this->equals($l->__tostring(), 'foo');
        }

        function testRandom()
        {
            $l = new Loop('foo', 'bar', 'charlie');
            $arr = array('foo', 'bar', 'charlie');
            $this->true(in_array($l->rand(), $arr));
            $this->true(in_array($l->rand(), $arr));
            $this->true(in_array($l->rand(), $arr));
            $this->true(in_array($l->rand(), $arr));
        }
    }

    $t = new TestLoop();