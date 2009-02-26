<?PHP
    // A quick and dirty command line argument parser. Written in about
    // an hour, so you might want to take this with a grain of salt or two.
    //
    // More info:
    // http://clickontyler.com/blog/2008/11/parse-command-line-arguments-in-php/
    //
    // Single letter options should be prefixed with a single
    // dash and can be grouped together. Examples:
    //
    // cmd -a
    // cmd -ab
    //
    // Values can be assigned to single letter options like so:
    //
    // cmd -a foo (a will be set to foo.)
    //
    // cmd -a foo -b (a will be set to foo.)
    //
    // cmd -ab foo (a and b will simply be set to true. foo is only listed as an argument.)
    //
    // You can also use the double-dash syntax. Examples:
    //
    // cmd --value
    //
    // cmd --value foo (value is set to foo)
    //
    // cmd --value=foo (value is set to foo)
    //
    // Single dash and double dash syntax may be mixed.
    //
    // Trailing arguments are treated as such. Examples:
    //
    // cmd -abc foo bar (foo and bar are listed as arguments)
    //
    // cmd -a foo -b bar charlie (only bar and charlie are arguments)


    class Args
    {
        private $flags;
        public $args;

        public function __construct()
        {
            $this->flags = array();
            $this->args  = array();

            $argv = $GLOBALS['argv'];
            array_shift($argv);

            for($i = 0; $i < count($argv); $i++)
            {
                $str = $argv[$i];

                // --foo
                if(strlen($str) > 2 && substr($str, 0, 2) == '--')
                {
                    $str = substr($str, 2);
                    $parts = explode('=', $str);
                    $this->flags[$parts[0]] = true;

                    // Does not have an =, so choose the next arg as its value
                    if(count($parts) == 1 && isset($argv[$i + 1]) && preg_match('/^--?.+/', $argv[$i + 1]) == 0)
                    {
                        $this->flags[$parts[0]] = $argv[$i + 1];
                    }
                    elseif(count($parts) == 2) // Has a =, so pick the second piece
                    {
                        $this->flags[$parts[0]] = $parts[1];
                    }
                }
                elseif(strlen($str) == 2 && $str[0] == '-') // -a
                {
                    $this->flags[$str[1]] = true;
                    if(isset($argv[$i + 1]) && preg_match('/^--?.+/', $argv[$i + 1]) == 0)
                        $this->flags[$str[1]] = $argv[$i + 1];
                }
                elseif(strlen($str) > 1 && $str[0] == '-') // -abcdef
                {
                    for($j = 1; $j < strlen($str); $j++)
                        $this->flags[$str[$j]] = true;
                }
            }

            for($i = count($argv) - 1; $i >= 0; $i--)
            {
                if(preg_match('/^--?.+/', $argv[$i]) == 0)
                    $this->args[] = $argv[$i];
                else
                    break;
            }

            $this->args = array_reverse($this->args);
        }

        public function flag($name)
        {
            return isset($this->flags[$name]) ? $this->flags[$name] : false;
        }
    }
