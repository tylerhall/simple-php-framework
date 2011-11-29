<?PHP
    class Database
    {
        // Singleton object. Leave $me alone.
        private static $me;

        public $readDB;
        public $writeDB;

        public $readHost;
        public $writeHost;

        public $name;

        public $readUsername;
        public $writeUsername;

        public $readPassword;
        public $writePassword;

        public $onError; // Can be '', 'die', or 'redirect'
        public $emailOnError;
        public $queries;
        public $result;

        public $emailTo; // Where to send an error report
        public $emailSubject;

        public $errorUrl; // Where to redirect the user on error

        // Singleton constructor
        private function __construct()
        {
            $this->readHost      = Config::get('dbReadHost');
            $this->writeHost     = Config::get('dbWriteHost');
            $this->name          = Config::get('dbName');
            $this->readUsername  = Config::get('dbReadUsername');
            $this->writeUsername = Config::get('dbWriteUsername');
            $this->readPassword  = Config::get('dbReadPassword');
            $this->writePassword = Config::get('dbWritePassword');
            $this->onError       = Config::get('dbOnError');
            $this->emailOnError  = Config::get('dbEmailOnError');

            $this->readDB  = false;
            $this->writeDB = false;
            $this->queries = array();
        }

        // Get Singleton object
        public static function getDatabase()
        {
            if(is_null(self::$me))
                self::$me = new Database();
            return self::$me;
        }

        // Do we have a valid read-only database connection?
        public function isReadConnected()
        {
            return is_resource($this->readDB) && get_resource_type($this->readDB) == 'mysql link';
        }

        // Do we have a valid read/write database connection?
        public function isWriteConnected()
        {
            return is_resource($this->writeDB) && get_resource_type($this->writeDB) == 'mysql link';
        }

        // Do we have a valid database connection and have we selected a database?
        public function databaseSelected()
        {
            if(!$this->isReadConnected()) return false;
            $result = mysql_list_tables($this->name, $this->readDB);
            return is_resource($result);
        }

        public function readConnect()
        {
            $this->readDB = mysql_connect($this->readHost, $this->readUsername, $this->readPassword) or $this->notify();
            if($this->readDB === false) return false;

            if(!empty($this->name))
                mysql_select_db($this->name, $this->readDB) or $this->notify();

            return $this->isReadConnected();
        }

        public function writeConnect()
        {
            $this->writeDB = mysql_connect($this->writeHost, $this->writeUsername, $this->writePassword) or $this->notify();
            if($this->writeDB === false) return false;

            if(!empty($this->name))
                mysql_select_db($this->name, $this->writeDB) or $this->notify();

            return $this->isWriteConnected();
        }

        public function query($sql, $args_to_prepare = null, $exception_on_missing_args = true)
        {
            // Read or Write connection?
            $sql = trim($sql);
            if(preg_match('/^(INSERT|UPDATE|REPLACE|DELETE)/i', $sql) == 0)
            {
                if(!$this->isReadConnected())
                    $this->readConnect();

                $the_db = $this->readDB;
            }
            else
            {
                if(!$this->isWriteConnected())
                    $this->writeConnect();

                $the_db = $this->writeDB;
            }

            // Allow for prepared arguments. Example:
            // query("SELECT * FROM table WHERE id = :id:", array('id' => $some_val));
            if(is_array($args_to_prepare))
            {
                foreach($args_to_prepare as $name => $val)
                {
					if(!is_int($val)) $val = $this->quote($val);
                    $sql = str_replace(":$name:", $val, $sql, $count);
                    if($exception_on_missing_args && (0 == $count))
                        throw new Exception(":$name: was not found in prepared SQL query.");
                }
            }

            $this->queries[] = $sql;
            $this->result = mysql_query($sql, $the_db) or $this->notify();
            return $this->result;
        }

        // Returns the number of rows.
        // You can pass in nothing, a string, or a db result
        public function numRows($arg = null)
        {
            $result = $this->resulter($arg);
            return ($result !== false) ? mysql_num_rows($result) : false;
        }

        // Returns true / false if the result has one or more rows
        public function hasRows($arg = null)
        {
            $result = $this->resulter($arg);
            return is_resource($result) && (mysql_num_rows($result) > 0);
        }

        // Returns the number of rows affected by the previous WRITE operation
        public function affectedRows()
        {
            if(!$this->isWriteConnected()) return false;
            return mysql_affected_rows($this->writeDB);
        }

        // Returns the auto increment ID generated by the previous insert statement
        public function insertId()
        {
            if(!$this->isWriteConnected()) return false;
            return mysql_insert_id($this->writeDB);
        }

        // Returns a single value.
        // You can pass in nothing, a string, or a db result
        public function getValue($arg = null)
        {
            $result = $this->resulter($arg);
            return $this->hasRows($result) ? mysql_result($result, 0, 0) : false;
        }

        // Returns an array of the first value in each row.
        // You can pass in nothing, a string, or a db result
        public function getValues($arg = null)
        {
            $result = $this->resulter($arg);
            if(!$this->hasRows($result)) return array();

            $values = array();
            mysql_data_seek($result, 0);
            while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                $values[] = array_pop($row);
            return $values;
        }

        // Returns the first row.
        // You can pass in nothing, a string, or a db result
        public function getRow($arg = null)
        {
            $result = $this->resulter($arg);
            return $this->hasRows($result) ? mysql_fetch_array($result, MYSQL_ASSOC) : false;
        }

        // Returns an array of all the rows.
        // You can pass in nothing, a string, or a db result
        public function getRows($arg = null)
        {
            $result = $this->resulter($arg);
            if(!$this->hasRows($result)) return array();

            $rows = array();
            mysql_data_seek($result, 0);
            while($row = mysql_fetch_array($result, MYSQL_ASSOC))
                $rows[] = $row;
            return $rows;
        }

        // Escapes a value and wraps it in single quotes.
        public function quote($var)
        {
            return "'" . $this->escape($var) . "'";
        }

        // Escapes a value.
        public function escape($var)
        {
            if(!$this->isReadConnected()) $this->readConnect();
            return mysql_real_escape_string($var, $this->readDB);
        }

        public function numQueries()
        {
            return count($this->queries);
        }

        public function lastQuery()
        {
            if($this->numQueries() > 0)
                return $this->queries[$this->numQueries() - 1];
            else
                return false;
        }

        private function notify()
        {
            if($this->emailOnError === true)
            {
                $globals = print_r($GLOBALS, true);

                $msg = '';
                $msg .= "Url: " . full_url() . "\n";
                $msg .= "Date: " . dater() . "\n";
                $msg .= "Server: " . $_SERVER['SERVER_NAME'] . "\n";

                $msg .= "ReadDB Error:\n" . mysql_error($this->readDB) . "\n\n";
                $msg .= "WriteDB Error:\n" . mysql_error($this->writeDB) . "\n\n";

                ob_start();
                debug_print_backtrace();
                $trace = ob_get_contents();
                ob_end_clean();

                $msg .= $trace . "\n\n";

                $msg .= $globals;

                mail($this->emailTo, $this->emailSubject, $msg);
            }

            if($this->onError == 'die')
            {
                echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Read Database Error:</strong><br/>" . mysql_error($this->readDB) . "</p>";
                echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Write Database Error:</strong><br/>" . mysql_error($this->writeDB) . "</p>";
                echo "<p style='border:5px solid red;background-color:#fff;padding:5px;'><strong>Last Query:</strong><br/>" . $this->lastQuery() . "</p>";
                echo "<pre>";
                debug_print_backtrace();
                echo "</pre>";
                exit;
            }

            if($this->onError == 'redirect')
            {
                redirect($this->errorUrl);
            }
        }

        // Takes nothing, a MySQL result, or a query string and returns
        // the correspsonding MySQL result resource or false if none available.
        private function resulter($arg = null)
        {
            if(is_null($arg) && is_resource($this->result))
                return $this->result;
            elseif(is_resource($arg))
                return $arg;
            elseif(is_string($arg))
            {
                $this->query($arg);
                if(is_resource($this->result))
                    return $this->result;
                else
                    return false;
            }
            else
                return false;
        }
    }
