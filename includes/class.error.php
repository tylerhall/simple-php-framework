<?PHP
    class Error
    {
        // Singleton object. Leave $me alone.
        private static $me;

        public $errors; // Array of errors
        public $style;  // CSS rules to apply to error elements

        private function __construct($style = "border:1px solid red;")
        {
            $this->errors = array();
            $this->style = $style;
        }

        // Get Singleton object
        public static function getError()
        {
            if(is_null(self::$me))
                self::$me = new Error();
            return self::$me;
        }

        // Returns an unordered list of error messages
        public function __tostring()
        {
            return $this->alert();
        }

        // Returns true if there are no errors
        public function ok()
        {
            return count($this->errors) == 0;
        }

        // Manually add an error
        public function add($id, $msg)
        {
            if(isset($this->errors[$id]) && !is_array($this->errors[$id]))
                $this->errors[$id] = array($msg);
            else
                $this->errors[$id][] = $msg;
        }

        // Delete all errors associated with an element's id
        public function delete($id)
        {
            unset($this->errors[$id]);
        }

        // Returns the error message associated with an element.
        // This may return a string or an array - so be sure to test before echoing!
        public function msg($id)
        {
            return $this->errors[$id];
        }

        // Outputs the CSS to style the error elements
        public function css($header = true)
        {
            $out = '';
            if(count($this->errors) > 0)
            {
                if($header) $out .= '<style type="text/css" media="screen">';
                $out .= "#" . implode(", #", array_keys($this->errors)) . " { {$this->style} }";
                if($header) $out .= '</style>';
            }
            echo $out;
        }

        // Returns an unordered list of error messages
        public function ul($class = 'warn')
        {
            if(count($this->errors) == 0) return '';

            $out = "<ul class='$class'>";
            foreach($this->errors as $error)
                $out .= "<li>" . implode("</li><li>", $error) . "</li>";
            $out .= "</ul>";

            return $out;
        }

        // Returns error alerts
        public function alert()
        {
            if(count($this->errors) == 0)
                return '';

            $out = '';
            foreach($this->errors as $error)
                $out .= "<p class='alert error'>" . implode(' ', $error) . "</p>";

            return $out;
        }

        // Below are a collection of tests for error conditions in your user's input...
        // Be sure to customize these to suit your app's needs. Especially the error messages.

        // Is the (string) value empty?
        public function blank($val, $id, $name = null)
        {
            if(trim($val) == '')
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name cannot be left blank.");
                return false;
            }

            return true;
        }

        // Is a number between a given range? (inclusive)
        public function range($val, $lower, $upper, $id, $name = null)
        {
            if($val < $lower || $val > $upper)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name must be between $lower and $upper.");
                return false;
            }

            return true;
        }

        // Is a string an appropriate length?
        public function length($val, $lower, $upper, $id, $name = null)
        {
            if(strlen($val) < $lower)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name must be at least $lower characters.");
                return false;
            }
            elseif(strlen($val) > $upper)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name cannot be more than $upper characters long.");
                return false;
            }

            return true;
        }

        // Do the passwords match?
        public function passwords($pass1, $pass2, $id)
        {
            if($pass1 !== $pass2)
            {
                $this->add($id, 'The passwords you entered do not match.');
                return false;
            }

            return true;
        }

        // Does a value match a given regex?
        public function regex($val, $regex, $id, $msg)
        {
            if(preg_match($regex, $val) === 0)
            {
                $this->add($id, $msg);
                return false;
            }

            return true;
        }

        // Is an email address valid?
        public function email($val, $id = 'email')
        {
            if(!preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $val))
            {
                $this->add($id, 'The email address you entered is not valid.');
                return false;
            }

            return true;
        }

        // Is a string a parseable and valid date?
        public function date($val, $id)
        {
            if(chkdate($val) === false)
            {
                $this->add($id, 'Please enter a valid date');
                return false;
            }

            return true;
        }

        // Is a birth date at least 18 years old?
        public function adult($val, $id)
        {
            if( dater($val) > ( (date('Y') - 18) . date('-m-d H:i:s') ) )
            {
                $this->add($id, 'You must be at least 18 years old.');
                return false;
            }

            return true;
        }

        // Is a string a valid phone number?
        public function phone($val, $id)
        {
            $val = preg_replace('/[^0-9]/', '', $val);
            if(strlen($val) != 7 && strlen($val) != 10)
            {
                $this->add($id, 'Please enter a valid 7 or 10 digit phone number.');
                return false;
            }

            return true;
        }

        // Did we get a successful file upload?
        // Typically, you'd pass in $_FILES['file']
        public function upload($val, $id)
        {
            if(!is_uploaded_file($val['tmp_name']) || !is_readable($val['tmp_name']))
            {
                $this->add($id, 'Your file was not uploaded successfully. Please try again.');
                return false;
            }

            return true;
        }

        // Valid 5 digit zip code?
        public function zip($val, $id, $name = null)
        {
            // From http://www.zend.com//code/codex.php?ozid=991&single=1
            $ranges = array(array('99500', '99929'), array('35000', '36999'), array('71600', '72999'), array('75502', '75505'), array('85000', '86599'), array('90000', '96199'), array('80000', '81699'), array('06000', '06999'), array('20000', '20099'), array('20200', '20599'), array('19700', '19999'), array('32000', '33999'), array('34100', '34999'), array('30000', '31999'), array('96700', '96798'), array('96800', '96899'), array('50000', '52999'), array('83200', '83899'), array('60000', '62999'), array('46000', '47999'), array('66000', '67999'), array('40000', '42799'), array('45275', '45275'), array('70000', '71499'), array('71749', '71749'), array('01000', '02799'), array('20331', '20331'), array('20600', '21999'), array('03801', '03801'), array('03804', '03804'), array('03900', '04999'), array('48000', '49999'), array('55000', '56799'), array('63000', '65899'), array('38600', '39799'), array('59000', '59999'), array('27000', '28999'), array('58000', '58899'), array('68000', '69399'), array('03000', '03803'), array('03809', '03899'), array('07000', '08999'), array('87000', '88499'), array('89000', '89899'), array('00400', '00599'), array('06390', '06390'), array('09000', '14999'), array('43000', '45999'), array('73000', '73199'), array('73400', '74999'), array('97000', '97999'), array('15000', '19699'), array('02800', '02999'), array('06379', '06379'), array('29000', '29999'), array('57000', '57799'), array('37000', '38599'), array('72395', '72395'), array('73300', '73399'), array('73949', '73949'), array('75000', '79999'), array('88501', '88599'), array('84000', '84799'), array('20105', '20199'), array('20301', '20301'), array('20370', '20370'), array('22000', '24699'), array('05000', '05999'), array('98000', '99499'), array('49936', '49936'), array('53000', '54999'), array('24700', '26899'), array('82000', '83199'));
            foreach($ranges as $r)
            {
                if($val >= $r[0] && $val <= $r[1])
                    return true;
            }

            if(is_null($name)) $name = ucwords($id);
            $this->add($id, "Please enter a valid, 5-digit zip code.");
            return false;
        }

        // Test if string $val is a valid, decimal number.
        public function nan($val, $id, $name = null)
        {
            if(preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $val) == 0)
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name must be a number.");
                return false;
            }
            return true;
        }

        // Valid URL?
        // This is hardly perfect, but it's good enough for now...
        // TODO: Make URL validation more robust
        public function url($val, $id, $name = null)
        {
            $info = @parse_url($val);
            if(($info === false) || ($info['scheme'] != 'http' && $info['scheme'] != 'https') || ($info['host'] == ''))
            {
                if(is_null($name)) $name = ucwords($id);
                $this->add($id, "$name is not a valid URL.");
                return false;
            }
            return true;
        }
    }
