<?PHP
    // Stores session variables unique to a given URL
    class PagePref
    {
        public $_id;
        public $_data;

        public function __construct()
        {
            $this->_id = 'pp' . md5($_SERVER['PHP_SELF']);

            if(isset($_SESSION[$this->_id]))
                $this->_data = unserialize($_SESSION[$this->_id]);
        }

        public function __get($key)
        {
            return $this->_data[$key];
        }

        public function __set($key, $val)
        {
            if(!is_array($this->_data)) $this->_data = array();
            $this->_data[$key] = $val;
            $_SESSION[$this->_id] = serialize($this->_data);
        }

        public function clear()
        {
            unset($_SESSION[$this->_id]);
            unset($this->_data);
        }
    }
