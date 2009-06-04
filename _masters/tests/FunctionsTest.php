<?php
require_once 'PHPUnit/Framework.php';
require_once 'includes/functions.inc.php';
 
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    function testSlugify()
    {
        $this->assertEquals('hello-this-is-a-test-123', slugify('Hello! This is a test 1...2...3...'));
    }

    function testCalendar()
    {
        for($year = 1998; $year <= 2010; $year++)
        {
            for($month = 1; $month <= 12; $month++)
            {
                $this->info = "$month/$year";
                $this->assertEquals(5, count(calendar($month, $year)), '', 1);
            }
        }
    }

    function testPickOff()
    {
        $_SERVER['REQUEST_URI'] = '/people/tylerhall/tags/apple';
        $ret = pick_off();
        if($ret['people'] != 'tylerhall' || $ret['tags'] != 'apple' || count($ret) != 2)
            $this->fail($_SERVER['REQUEST_URI']);
        else
            return;

        $_SERVER['REQUEST_URI'] = '/people/tylerhall/tags/apple';
        $ret = pick_off(false);
        if($ret['people'] != 'tylerhall' || $ret['tags'] != 'apple' || count($ret) != 2)
            $this->fail($_SERVER['REQUEST_URI']);
        else
            return;

        $_SERVER['REQUEST_URI'] = '/newest/people/tylerhall/tags/apple';
        $ret = pick_off(true);
        if($ret[0] != 'newest' || $ret['people'] != 'tylerhall' || $ret['tags'] != 'apple' || count($ret) != 3)
            $this->fail($_SERVER['REQUEST_URI']);
        else
            return;
    }

    function testDater()
    {
        $ts = time();
        $date = date('Y-m-d H:i:s', $ts);

        $this->assertEquals($date, dater($ts, 'Y-m-d H:i:s'));
        $this->assertEquals($date, dater($date, 'Y-m-d H:i:s'));
        $this->assertEquals($date, dater(strtotime($date), 'Y-m-d H:i:s'));
    }

    function testFormatPhone()
    {
        $this->assertEquals("(615) 429-5938", format_phone('6154295938'));
        $this->assertEquals("(615) 429-5938", format_phone('615-429-5938'));
        $this->assertEquals("(615) 429-5938", format_phone("(615)-429-5938"));
        $this->assertEquals("(615) 429-5938", format_phone('615.429.5938'));
        $this->assertEquals('429-5938', format_phone('4295938'));
        $this->assertEquals('429-5938', format_phone('429-5938'));
        $this->assertEquals('429-5938', format_phone('429-5938'));
        $this->assertEquals('429-5938', format_phone('429.5938'));
        $this->assertEquals('429-5938', format_phone("429.ASF*^&%AS*^5938"));
    }

    function testRemoteFilesize()
    {
        $this->assertEquals('132729', remote_filesize("http://s3.amazonaws.com/amz.clickontyler.com/blog/105home.png"));
    }

    function testBytes2Str()
    {
        $arr = array('0.9B', '1B', '1.1B', '921.6B', '1KB', '1.1KB', '921.6KB', '1MB', '1.1MB', '921.6MB', '1GB', '1.1GB', '921.6GB', '1TB', '1.1TB', '921.6TB', '1PB', '1.1PB', '921.6PB', '1EB', '1.1EB', '921.6EB', '1ZB', '1.1ZB', '921.6ZB', '1YB', '1.1YB');
        for($i = 0; $i < 9; $i++)
        {
            $this->assertEquals(array_shift($arr), bytes2str(pow(1024, $i) *.9, 2));
            $this->assertEquals(array_shift($arr), bytes2str(pow(1024, $i), 2));
            $this->assertEquals(array_shift($arr), bytes2str(pow(1024, $i) * 1.1, 2));
        }
    }

    function testSlash()
    {
        $this->assertEquals('foobar/',slash('foobar/'));
        $this->assertEquals('/foobar/', slash('/foobar/'));
        $this->assertEquals('/foobar/', slash('/foobar'));
        $this->assertEquals('/foobar/', slash('/foobar///'));
    }

    function testUnslash()
    {
        $this->assertEquals('foobar', unslash('foobar/'));
        $this->assertEquals('/foobar', unslash('/foobar/'));
        $this->assertEquals('/foobar', unslash('/foobar'));
        $this->assertEquals('/foobar', unslash('/foobar///'));
    }

    function testGimme()
    {
        $arr = array(array(1, 3, 5, 7, 9),
                     array(2, 4, 5, 6, 1),
                     array(3, 1, 4, 4, 5));
        $this->assertEquals(array(1, 2, 3), gimme($arr));
        $this->assertEquals(array(3, 4, 1), gimme($arr, 1));
        $this->assertEquals(array(9, 1, 5), gimme($arr, 4));
    }

    function testValidEmail()
    {
        $this->assertTrue(valid_email("email@foo123.com"));
        $this->assertTrue(valid_email("my1email@123foo.com"));
        $this->assertTrue(valid_email("123mail@fo220o.com"));
        $this->assertTrue(valid_email("mail-123.hello@foo.com"));
        $this->assertTrue(valid_email("email+bucket@foo.com"));
        $this->assertTrue(valid_email("tylerhall@gmail.com", true));
        $this->assertFalse(valid_email("em ail@foo.com"));
        $this->assertFalse(valid_email("em\ail@foo.com"));
        $this->assertFalse(valid_email("em ail@ foo.com"));
        $this->assertFalse(valid_email("em ail@foo."));
        $this->assertFalse(valid_email("@"));
        $this->assertFalse(valid_email(''));
        $this->assertFalse(valid_email(' '));
    }

    function testMatch()
    {
        $str = 'aaabbbcdefg';
        $this->assertEquals('aaabbb', match('/[a-b]+/', $str));
        $this->assertEquals('bbbc', match('/a+(b*c)c*/', $str, 1));
        $this->assertFalse(match('/c[a-b]+/', $str));
    }

    function testPick()
    {
        $this->assertEquals(1, pick(1, 2, 3));
        $this->assertEquals(2, pick('', 2, 3));
        $this->assertEquals(2, pick(null, 2, 3));
        $this->assertEquals(2, pick(0, 2, 3));
        $this->assertEquals(3, pick('', '', 3));
        $this->assertEquals('', pick());
        $this->assertEquals(1, pick(1));
    }
}