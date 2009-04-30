<?PHP
    class DBObject
    {
        public $id;
        public $tableName;
        public $idColumnName;

        protected $columns = array();
        protected $className;

        protected function __construct($table_name, $columns, $id = null)
        {
            $this->className    = get_class($this);
            $this->tableName    = $table_name;

            // A note on hardcoding $this->idColumnName = 'id'...
            // In three years working with this framework, I've used
            // a different id name exactly once - so I've decided to
            // drop the option from the constructor. You can overload
            // the constructor yourself if you have the need.
            $this->idColumnName = 'id';

            foreach($columns as $col)
                $this->columns[$col] = null;

            if(!is_null($id))
                $this->select($id);
        }

        public function __get($key)
        {
            if(array_key_exists($key, $this->columns))
                return $this->columns[$key];

            if((substr($key, 0, 2) == '__') && array_key_exists(substr($key, 2), $this->columns))
                return htmlspecialchars($this->columns[substr($key, 2)]);

            $trace = debug_backtrace();
            trigger_error("Undefined property via DBObject::__get(): $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
            return null;
        }

        public function __set($key, $value)
        {
            if(array_key_exists($key, $this->columns))
                $this->columns[$key] = $value;

            return $value; // Seriously.
        }

        public function select($id, $column = null)
        {
            $db = Database::getDatabase();

            if(is_null($column)) $column = $this->idColumnName;
            $column = $db->escape($column);

            $db->query("SELECT * FROM `{$this->tableName}` WHERE `$column` = :id LIMIT 1", array('id' => $id));
            if($db->hasRows())
            {
                $row = $db->getRow();
                $this->load($row);
                return true;
            }

            return false;
        }

        public function ok()
        {
            return !is_null($this->id);
        }

        public function save()
        {
            if(is_null($this->id))
                $this->insert();
            else
                $this->update();
            return $this->id;
        }

        public function insert($cmd = 'INSERT INTO')
        {
            $db = Database::getDatabase();

            if(count($this->columns) == 0) return false;

            $data = array();
            foreach($this->columns as $k => $v)
                if(!is_null($v))
                    $data[$k] = $db->quote($v);

            $columns = '`' . implode('`, `', array_keys($data)) . '`';
            $values = implode(',', $data);

            $db->query("$cmd `{$this->tableName}` ($columns) VALUES ($values)");
            $this->id = $db->insertId();
            return $this->id;
        }

        public function replace()
        {
            return $this->delete() && $this->insert();
        }

        public function update()
        {
            if(is_null($this->id)) return false;

            $db = Database::getDatabase();

            if(count($this->columns) == 0) return;

            $sql = "UPDATE {$this->tableName} SET ";
            foreach($this->columns as $k => $v)
                $sql .= "`$k`=" . $db->quote($v) . ',';
            $sql[strlen($sql) - 1] = ' ';

            $sql .= "WHERE `{$this->idColumnName}` = " . $db->quote($this->id);
            $db->query($sql);

            return $db->affectedRows();
        }

        public function delete()
        {
            if(is_null($this->id)) return false;
            $db = Database::getDatabase();
            $db->query("DELETE FROM `{$this->tableName}` WHERE `{$this->idColumnName}` = :id LIMIT 1", array('id' => $this->id));
            return $db->affectedRows();
        }

        public function load($row)
        {
            foreach($row as $k => $v)
            {
                if($k == $this->idColumnName)
                    $this->id = $v;
                elseif(array_key_exists($k, $this->columns))
                    $this->columns[$k] = $v;
            }
        }

        // Grabs a large block of instantiated $class_name objects from the database using only one query.
        public static function glob($class_name, $sql = null, $extra_columns = array())
        {
            $db = Database::getDatabase();

            // Make sure the class exists before we instantiate it...
            if(!class_exists($class_name))
                return false;

            $tmp_obj = new $class_name;

            // Also, it needs to be a subclass of DBObject...
            if(!is_subclass_of($tmp_obj, 'DBObject'))
                return false;

            if(is_null($sql))
                $sql = "SELECT * FROM `{$tmp_obj->tableName}`";

            $objs = array();
            $rows = $db->getRows($sql);
            foreach($rows as $row)
            {
                $o = new $class_name;
                $o->load($row);
                $objs[$o->id] = $o;

                foreach($extra_columns as $c)
                {
                    $o->addColumn($c);
                    $o->$c = isset($row[$c]) ? $row[$c] : null;
                }
            }
            return $objs;
        }

        public function addColumn($key, $val = null)
        {
            if(!in_array($key, array_keys($this->columns)))
                $this->columns[$key] = $val;
        }
    }

    class TaggableDBObject extends DBObject
    {
        protected $tagColumnName;

        public function __construct($table_name, $columns, $id = null)
        {
            parent::__construct($table_name, $columns, $id);
            $this->tagColumnName = strtolower($this->className . '_id');
        }

        public function addTag($name)
        {
            $db = Database::getDatabase();

            if(is_null($this->id)) return false;

            $name = trim($name);
            if($name == '') return false;

            $t = new Tag($name);
            $db->query("INSERT IGNORE {$this->tableName}2tags ({$this->tagColumnName}, tag_id) VALUES (:obj_id, :tag_id)", array('obj_id' => $this->id, 'tag_id' => $t->id));
            return true;
        }

        public function removeTag($name)
        {
            $db = Database::getDatabase();

            if(is_null($this->id)) return false;

            $name = trim($name);
            if($name == '') return false;

            $t = new Tag($name);
            $db->query("DELETE FROM {$this->tableName}2tags WHERE {$this->tagColumnName} = :obj_id AND tag_id = :tag_id", array('obj_id' => $this->id, 'tag_id' => $t->id));
            return true;
        }

        public function clearTags()
        {
            $db = Database::getDatabase();
            if(is_null($this->id)) return false;
            $db->query("DELETE FROM {$this->tableName}2tags WHERE {$this->tagColumnName} = :obj_id", array('obj_id' => $this->id));
            return true;
        }

        public function tags()
        {
            $db = Database::getDatabase();
            if(is_null($this->id)) return false;
            $result = $db->query("SELECT t.id, t.name FROM {$this->tableName}2tags a LEFT JOIN tags t ON a.tag_id = t.id WHERE a.{$this->tagColumnName} = '{$this->id}'");
            $tags = array();
            $rows = $db->getRows($result);
            foreach($rows as $row)
                $tags[$row['name']] = $row['id'];
            return $tags;
        }

        // Return all objects tagged $tag_name
        public function tagged($tag_name, $sql = '')
        {
            $db = Database::getDatabase();

            $tag = new Tag($tag_name);
            if(is_null($tag->id)) return array();

            return DBObject::glob(get_class($this), "SELECT b.* FROM {$this->tableName}2tags a LEFT JOIN {$this->tableName} b ON a.{$this->tagColumnName} = b.{$this->idColumnName} WHERE a.tag_id = {$tag->id} $sql");
        }
    }
