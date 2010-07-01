<?PHP
    class DBLoop implements Iterator, Countable
    {
        private $position;
        private $className;
        private $extraColumns;
        private $result;

        public function __construct($class_name, $sql = null, $extra_columns = array())
        {
            $this->position     = 0;
            $this->className    = $class_name;
            $this->extraColumns = $extra_columns;

            // Make sure the class exists before we instantiate it...
            if(!class_exists($class_name))
                return;

            $tmp_obj = new $class_name;

            // Also, it needs to be a subclass of DBObject...
            if(!is_subclass_of($tmp_obj, 'DBObject'))
                return;

            if(is_null($sql))
                $sql = "SELECT * FROM `{$tmp_obj->tableName}`";

            $db = Database::getDatabase();
            $this->result = $db->query($sql);
        }

        public function rewind()
        {
            $this->position = 0;
        }

        public function current()
        {
            mysql_data_seek($this->result, $this->position);
            $row = mysql_fetch_array($this->result, MYSQL_ASSOC);
            if($row === false)
                return false;

            $o = new $this->className;
            $o->load($row);

            foreach($this->extraColumns as $c)
            {
                $o->addColumn($c);
                $o->$c = isset($row[$c]) ? $row[$c] : null;
            }

            return $o;
        }

        public function key()
        {
            return $this->position;
        }

        public function next()
        {
            $this->position++;
        }

        public function valid()
        {
            if($this->position < mysql_num_rows($this->result))
                return mysql_data_seek($this->result, $this->position);
            else
                return false;
        }

        public function count()
        {
            return mysql_num_rows($this->result);
        }
    }
