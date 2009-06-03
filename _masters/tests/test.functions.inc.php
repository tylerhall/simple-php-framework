<?PHP
    require_once 'class.test.php';
    require_once '../../includes/functions.inc.php';

    class TestFunctions extends Test
    {
        function testSlugify()
        {
            $this->equals(slugify('Hello! This is a test 1...2...3...'), 'hello-this-is-a-test-123');
        }

        function testCalendar()
        {
            for($year = 1998; $year <= 2010; $year++)
            {
                for($month = 1; $month <= 12; $month++)
                {
                    $this->info = "$month/$year";
                    $this->range(count(calendar($month, $year)), 4, 6);
                }
            }
        }

        function testPickOff()
        {
            $_SERVER['REQUEST_URI'] = '/people/tylerhall/tags/apple';
            $ret = pick_off();
            if($ret['people'] != 'tylerhall' || $ret['tags'] != 'apple' || count($ret) != 2)
                $this->doFail($_SERVER['REQUEST_URI']);
            else
                $this->doPass();

            $_SERVER['REQUEST_URI'] = '/people/tylerhall/tags/apple';
            $ret = pick_off(false);
            if($ret['people'] != 'tylerhall' || $ret['tags'] != 'apple' || count($ret) != 2)
                $this->doFail($_SERVER['REQUEST_URI']);
            else
                $this->doPass();

            $_SERVER['REQUEST_URI'] = '/newest/people/tylerhall/tags/apple';
            $ret = pick_off(true);
            if($ret[0] != 'newest' || $ret['people'] != 'tylerhall' || $ret['tags'] != 'apple' || count($ret) != 3)
                $this->doFail($_SERVER['REQUEST_URI']);
            else
                $this->doPass();
        }

        function testDater()
        {
            $ts = time();
            $date = date('Y-m-d H:i:s', $ts);

            $this->equals(dater($ts, 'Y-m-d H:i:s'), $date);
            $this->equals(dater($date, 'Y-m-d H:i:s'), $date);
            $this->equals(dater(strtotime($date), 'Y-m-d H:i:s'), $date);
        }

        function testFormatPhone()
        {
            $this->equals(format_phone('6154295938'), "(615) 429-5938");
            $this->equals(format_phone('615-429-5938'), "(615) 429-5938");
            $this->equals(format_phone("(615)-429-5938"), "(615) 429-5938");
            $this->equals(format_phone('615.429.5938'), "(615) 429-5938");
            $this->equals(format_phone('4295938'), '429-5938');
            $this->equals(format_phone('429-5938'), '429-5938');
            $this->equals(format_phone('429-5938'), '429-5938');
            $this->equals(format_phone('429.5938'), '429-5938');
            $this->equals(format_phone("429.ASF*^&%AS*^5938"), '429-5938');
        }

        function testRemoteFilesize()
        {
            $this->equals(remote_filesize("http://s3.amazonaws.com/amz.clickontyler.com/blog/105home.png"), '132729');
        }

        function testBytes2Str()
        {
            $arr = array('0.9B', '1B', '1.1B', '921.6B', '1KB', '1.1KB', '921.6KB', '1MB', '1.1MB', '921.6MB', '1GB', '1.1GB', '921.6GB', '1TB', '1.1TB', '921.6TB', '1PB', '1.1PB', '921.6PB', '1EB', '1.1EB', '921.6EB', '1ZB', '1.1ZB', '921.6ZB', '1YB', '1.1YB');
            for($i = 0; $i < 9; $i++)
            {
                $this->equals(bytes2str(pow(1024, $i) *.9, 2), array_shift($arr));
                $this->equals(bytes2str(pow(1024, $i), 2), array_shift($arr));
                $this->equals(bytes2str(pow(1024, $i) * 1.1, 2), array_shift($arr));
            }
        }

        function testSlash()
        {
            $this->equals(slash('foobar/'), 'foobar/');
            $this->equals(slash('/foobar/'), '/foobar/');
            $this->equals(slash('/foobar'), '/foobar/');
            $this->equals(slash('/foobar///'), '/foobar/');
        }

        function testUnslash()
        {
            $this->equals(unslash('foobar/'), 'foobar');
            $this->equals(unslash('/foobar/'), '/foobar');
            $this->equals(unslash('/foobar'), '/foobar');
            $this->equals(unslash('/foobar///'), '/foobar');
        }
        
        function testGimme()
        {
            $arr = array(array(1, 3, 5, 7, 9),
                         array(2, 4, 5, 6, 1),
                         array(3, 1, 4, 4, 5));
            $this->true(array(1, 2, 3) === gimme($arr));
            $this->true(array(3, 4, 1) === gimme($arr, 1));
            $this->true(array(9, 1, 5) === gimme($arr, 4));
        }

        function testValidEmail()
        {
            $this->true(valid_email("email@foo123.com"));
            $this->true(valid_email("my1email@123foo.com"));
            $this->true(valid_email("123mail@fo220o.com"));
            $this->true(valid_email("mail-123.hello@foo.com"));
            $this->true(valid_email("email+bucket@foo.com"));
            $this->true(valid_email("tylerhall@gmail.com", true));
            $this->false(valid_email("em ail@foo.com"));
            $this->false(valid_email("em\ail@foo.com"));
            $this->false(valid_email("em ail@ foo.com"));
            $this->false(valid_email("em ail@foo."));
            $this->false(valid_email("@"));
            $this->false(valid_email(''));
            $this->false(valid_email(' '));
        }

        function testMatch()
        {
            $str = 'aaabbbcdefg';
            $this->equals(match('/[a-b]+/', $str), 'aaabbb');
            $this->equals(match('/a+(b*c)c*/', $str, 1), 'bbbc');
            $this->false(match('/c[a-b]+/', $str));
        }

        function testPick()
        {
            $this->equals(pick(1, 2, 3), 1);
            $this->equals(pick('', 2, 3), 2);
            $this->equals(pick(null, 2, 3), 2);
            $this->equals(pick(0, 2, 3), 2);
            $this->equals(pick('', '', 3), 3);
            $this->equals(pick(), '');
            $this->equals(pick(1), 1);
        }
    }

    $t = new TestFunctions();