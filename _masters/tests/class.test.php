<?PHP
    class Test
    {
        public $log;
        public $numPass;
        public $numFail;
        public $info;

        public function __construct()
        {
            $this->log     = array();
            $this->numPass = 0;
            $this->numFail = 0;

            $this->runTests();
            $this->outputText();
        }

        public function runTests()
        {
            foreach(get_class_methods(get_class($this)) as $m)
            {
                if(preg_match('/^test[A-Z].*/', $m) > 0)
                {
                    $this->info = null;
                    $this->$m();
                }
            }
        }

        public function outputText()
        {
            foreach($this->log as $log)
            {
                if($log['result'] == 'fail')
                {
                    echo "*** {$log['function']}\n";
                    echo "Test: {$log['test']}\n";
                    echo "val: {$log['args'][0]}\n";
                    for($i = 1; $i < count($log['args']); $i++)
                        echo "arg$i: {$log['args'][$i]}\n";
                    if(isset($this->info))
                        echo "Info: {$this->info}\n";
                    echo "\n";
                }
                elseif($log['result'] == 'pass')
                {
                    // echo "PASSED {$log['function']}\n";
                }
            }

            echo get_class($this) . ' - ';
            if($this->numFail == 0)
                echo "Passed {$this->numPass} tests successfully.\n";
            else
                echo "Failed {$this->numFail} tests.\n";
        }

        public function doFail()
        {
            $this->fail();
        }

        public function doPass()
        {
            $this->pass();
        }

        private function fail()
        {
            $bt              = debug_backtrace();
            $arr             = array();
            $arr['function'] = $bt[2]['function'];
            $arr['result']   = 'fail';
            $arr['test']     = $bt[1]['function'];
            $arr['args']     = $bt[1]['args'];
            $this->log[]     = $arr;
            $this->numFail++;
        }

        private function pass()
        {
            $bt              = debug_backtrace();
            $arr             = array();
            $arr['function'] = $bt[2]['function'];
            $arr['result']   = 'pass';
            $arr['test']     = $bt[1]['function'];
            $arr['args']     = $bt[1]['args'];
            $this->log[]     = $arr;
            $this->numPass++;
        }

        public function true($val)
        {
            if($val !== true)
                $this->fail();
            else
                $this->pass();
        }

        public function false($val)
        {
            if($val !== false)
                $this->fail();
            else
                $this->pass();
        }

        public function equals($val, $exp1)
        {
            if($val != $exp1)
                $this->fail();
            else
                $this->pass();
        }

        public function range($val, $min, $max)
        {
            if($val < $min || $val > $max)
                $this->fail();
            else
                $this->pass();
        }
    }