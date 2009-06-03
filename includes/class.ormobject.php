<?PHP
    // ORMObject is brand new and totally untested as of 04/02/2009 - RTH
    class ORMObject extends DBObject
    {
        public static $_data;

        public function __construct($table_name, $columns, $id = null)
        {
            if(!is_array(ORMObject::$_data))
                ORMObject::$_data = array();

            parent::__construct($table_name, $columns, $id);
        }

        public function __get($key)
        {
            if(array_key_exists($key, $this->columns))
                return $this->columns[$key];

            if((substr($key, 0, 2) == '__') && array_key_exists(substr($key, 2), $this->columns))
                return htmlspecialchars($this->columns[substr($key, 2)]);

            if(isset(ORMObject::$_data[$this->className][strtolower($key)]))
                return $this->{ORMObject::$_data[$this->className][strtolower($key)]['getter']}($key);

            $trace = debug_backtrace();
            trigger_error("Undefined property via ORMObject::__get(): $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
            return null;
        }

        public function __set($key, $value)
        {
            if(array_key_exists($key, $this->columns))
                $this->columns[$key] = $value;
            elseif(isset(ORMObject::$_data[$this->className][strtolower($key)]))
                return $this->{ORMObject::$_data[$this->className][strtolower($key)]['setter']}($key, $value);

            return $value;
        }

        public function __call($name, $arguments)
        {
            if(isset(ORMObject::$_data[$this->className][strtolower($name)]))
                return $this->{ORMObject::$_data[$this->className][strtolower($name)]['func']}($name, $arguments);
        }

        // To be made in the object with the foreign key
        public function belongsTo($class_name, $pk = null, $fk = null)
        {
            if(is_null($pk)) $pk = 'id';
            if(is_null($fk)) $fk = strtolower($class_name . '_id');
            if(@!is_array(ORMObject::$_data[$this->className])) ORMObject::$_data[$this->className] = array();
            ORMObject::$_data[$this->className][strtolower($class_name)] =
                    array('pk' => $pk,
                          'fk' => $fk,
                          'pc' => $class_name,
                          'fc' => $this->className,
                          'setter' => 'setBelongsTo',
                          'getter' => 'getBelongsTo');
        }

        protected function getBelongsTo($key)
        {
            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $obj = new $data['pc'];
            $obj->select($this->{$data['fk']}, $data['pk']);
            return is_null($obj->id) ? null : $obj;
        }

        protected function setBelongsTo($key, $val)
        {
            if(!is_subclass_of($val, 'DBObject'))
            {
                trigger_error("Cannont assign non-DBObject to ORMObject property $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
                return;
            }

            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $this->{$data['fk']} = $val->{$data['pk']};
            $this->update();
        }

        // To be made in the object with the primary key
        public function hasOne($class_name, $pk = null, $fk = null)
        {
            if(is_null($pk)) $pk = 'id';
            if(is_null($fk)) $fk = strtolower($this->className . '_id');
            if(@!is_array(ORMObject::$_data[$this->className])) ORMObject::$_data[$this->className] = array();
            ORMObject::$_data[$this->className][strtolower($class_name)] =
                    array('pk' => $pk,
                          'fk' => $fk,
                          'pc' => $this->className,
                          'fc' => $class_name,
                          'setter' => 'setHasOne',
                          'getter' => 'getHasOne');
        }

        protected function getHasOne($key)
        {
            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $obj = new $data['fc'];
            $obj->select($this->{$data['pk']}, $data['fk']);
            return is_null($obj->id) ? null : $obj;
        }

        protected function setHasOne($key, $val)
        {
            $db = Database::getDatabase();

            if(!is_subclass_of($val, 'DBObject'))
            {
                trigger_error("Cannont assign non-DBObject to ORMObject property $key in {$trace[0]['file']} on line {$trace[0]['line']}", E_USER_NOTICE);
                return;
            }

            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $val->{$data['fk']} = $this->{$data['pk']};

            $db->query("DELETE FROM `{$val->tableName}` WHERE `{$data['fk']}` = :fk", array('fk' => $val->{$data['fk']}));
            $val->insert();
        }

        public function hasMany($class_name, $pk = null, $fk = null, $joined = false)
        {
            if(is_null($pk)) $pk = 'id';
            if(is_null($fk)) $fk = strtolower($this->className . '_id');
            if(@!is_array(ORMObject::$_data[$this->className])) ORMObject::$_data[$this->className] = array();
            ORMObject::$_data[$this->className][strtolower($class_name . 's')] =
                    array('pk' => $pk,
                          'fk' => $fk,
                          'pc' => $this->className,
                          'fc' => $class_name,
                          'getter' => 'getHasMany',
                          'joined' => $joined);

            ORMObject::$_data[$this->className][strtolower($class_name . 'Ids')] =
                    array('pk' => $pk,
                          'fk' => $fk,
                          'pc' => $this->className,
                          'fc' => $class_name,
                          'getter' => 'getHasManyIds',
                          'joined' => $joined);

            ORMObject::$_data[$this->className][strtolower('num' . ucfirst($class_name) . 's')] =
                    array('pk' => $pk,
                          'fk' => $fk,
                          'pc' => $this->className,
                          'fc' => $class_name,
                          'getter' => 'getHasManyCount',
                          'joined' => $joined);

            ORMObject::$_data[$this->className][strtolower('clear' . ucfirst($class_name) . 's')] =
                    array('pk' => $pk,
                          'fk' => $fk,
                          'pc' => $this->className,
                          'fc' => $class_name,
                          'func' => 'hasManyClear',
                          'joined' => $joined);
        }

        public function hasManyJoined($class_name, $pk = null, $fk = null)
        {
            $this->hasMany($class_name, $pk, $fk, true);
        }

        protected function getHasMany($key)
        {
            $db = Database::getDatabase();
            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $tmp_obj = new $data['fc'];

            if($data['joined'])
            {
                $join_table = $this->joinTable($this->tableName, $tmp_obj->tableName);
                $data_b = ORMObject::$_data[$tmp_obj->className][strtolower($this->className . 's')];
                $sql = "SELECT b.* FROM `$join_table` ab LEFT JOIN `$tmp_obj->tableName` b ON ab.{$data_b['fk']} = b.{$data_b['pk']} WHERE ab.{$data['fk']} = " . $db->quote($this->{$data['pk']});
                return DBObject::glob($data['fc'], $sql);
            }
            else
            {
                if(isset($data['sort']))
                    $sorter = " SORT BY `{$data['sort']}` ";
                else
                    $sorter = '';

                $sql = "SELECT * FROM `{$tmp_obj->tableName}` WHERE `{$data['fk']}` = " . $db->quote($this->id) . $sorter;
                return DBObject::glob($data['fc'], $sql);
            }
        }

        protected function getHasManyIds($key)
        {
            $db = Database::getDatabase();
            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $tmp_obj = new $data['fc'];

            if($data['joined'])
            {
                $join_table = $this->joinTable($this->tableName, $tmp_obj->tableName);
                $data_b = ORMObject::$_data[$tmp_obj->className][strtolower($this->className . 's')];
                $sql = "SELECT b.{$data_b['pk']} FROM `$join_table` ab LEFT JOIN `$tmp_obj->tableName` b ON ab.{$data_b['fk']} = b.{$data_b['pk']} WHERE ab.{$data['fk']} = " . $db->quote($this->{$data['pk']});
            }
            else
            {
                $sql = "SELECT `{$tmp_obj->idColumnName}` FROM `{$tmp_obj->tableName}` WHERE `{$data['fk']}` = " . $db->quote($this->id);
            }

            return $db->getValues($sql);
        }

        protected function getHasManyCount($key)
        {
            $db = Database::getDatabase();
            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $tmp_obj = new $data['fc'];

            if($data['joined'])
            {
                $join_table = $this->joinTable($this->tableName, $tmp_obj->tableName);
                $data_b = ORMObject::$_data[$tmp_obj->className][strtolower($this->className . 's')];
                $sql = "SELECT COUNT(*) FROM `$join_table` ab LEFT JOIN `$tmp_obj->tableName` b ON ab.{$data_b['fk']} = b.{$data_b['pk']} WHERE ab.{$data['fk']} = " . $db->quote($this->{$data['pk']});
            }
            else
            {
                $sql = "SELECT COUNT(*) FROM `{$tmp_obj->tableName}` WHERE `{$data['fk']}` = " . $db->quote($this->id);
            }

            return $db->getValue($sql);
        }

        protected function hasManyClear($key, $arguments = null)
        {
            $db = Database::getDatabase();
            $data = ORMObject::$_data[$this->className][strtolower($key)];
            $tmp_obj = new $data['fc'];

            if($data['joined'])
            {
                $join_table = $this->joinTable($this->tableName, $tmp_obj->tableName);
                $sql = "DELETE FROM `$join_table` WHERE `{$data['fk']}` = " . $db->quote($this->{$data['pk']});
            }
            else
            {
                $sql = "DELETE FROM `{$tmp_obj->tableName}` WHERE `{$data['fk']}` = " . $db->quote($this->id);
            }

            return $db->query($sql);
        }

        private function joinTable($a, $b)
        {
            $join_table = array($a, $b);
            sort($join_table);
            return implode('2', $join_table);
        }
    }
