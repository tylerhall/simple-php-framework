<?PHP
	class ORMObject extends DBObject
    {
		protected $belongsTo           = array();
		protected $hasOne              = array();
		protected $hasMany             = array();
		protected $hasManyAndBelongsTo = array();	

        public function __construct($table_name, $columns, $id = null)
        {
            parent::__construct($table_name, $columns, $id);
    	}

        public function __get($key)
        {
			$value = parent::__get($key);
			if(!is_null($value))
				return $value;

			if(array_key_exists($key, $this->belongsTo))
				return $this->getBelongsTo($key);

			if(array_key_exists($key, $this->hasOne))
				return $this->getHasOne($key);

			if(array_key_exists($key, $this->hasMany))
				return $this->getHasMany($key);

            return null;
        }

        public function __set($key, $value)
        {
            if(array_key_exists($key, $this->columns))
                $this->columns[$key] = $value;
			elseif(array_key_exists($key, $this->belongsTo))
				$this->setBelongsTo($key, $value);
			elseif(array_key_exists($key, $this->hasOne))
				$this->setHasOne($key, $value);

            return $value;
        }

		// To be made in the object with the foreign key
		public function belongsTo($class_name, $primary_key = null, $foreign_key = null)
		{
			if(is_null($primary_key))
				$primary_key = 'id';
			
			if(is_null($foreign_key))
				$foreign_key = strtolower($class_name . '_id');
		
			// lcfirst() in PHP 5.3
			$lcf_class_name = $class_name;
			$lcf_class_name[0] = strtolower($lcf_class_name[0]);
			
			$this->belongsTo[$lcf_class_name] = array('class_name' => $class_name, 'primary_key' => $primary_key, 'foreign_key' => $foreign_key);
		}
		
		protected function getBelongsTo($key)
		{
			$obj = new $this->belongsTo[$key]['class_name'];
			$obj->select($this->{$this->belongsTo[$key]['foreign_key']});
			return is_null($obj->id) ? null : $obj;
		}
		
		protected function setBelongsTo($key, $val)
		{
			$this->{$this->belongsTo[$key]['foreign_key']} = $val->{$this->belongsTo[$key]['primary_key']};
			$this->update();
		}
		
		// To be made in the object with the primary key
		public function hasOne($class_name, $primary_key = null, $foreign_key = null)
		{
			if(is_null($primary_key))
				$primary_key = 'id';
			
			if(is_null($foreign_key))
				$foreign_key = strtolower($class_name . '_id');

			// lcfirst() in PHP 5.3
			$lcf_class_name = $class_name;
			$lcf_class_name[0] = strtolower($lcf_class_name[0]);

			$this->hasOne[$lcf_class_name] = array('primary_key' => $primary_key, 'foreign_key' => $foreign_key);
		}

		protected function getHasOne($key)
		{
			$obj = new $this->hasOne[$key]['class_name'];
			$obj->select($this->id, $this->hasOne[$key]['foreign_key']);
			return is_null($obj->id) ? null : $obj;
		}
		
		protected function setHasOne($key, $val)
		{
			$val->{$this->hasOne[$key]['foreign_key']} = $this->id;
			$val->update();
		}
		
		public function hasMany($class_name, $primary_key = null, $foreign_key = null)
		{
			// cond
			// order
			// through
			
			if(is_null($primary_key))
				$primary_key = 'id';
			
			if(is_null($foreign_key))
				$foreign_key = strtolower($this->className . '_id');
				
			// lcfirst() in PHP 5.3
			$lcf_class_name = $class_name;
			$lcf_class_name[0] = strtolower($lcf_class_name[0]);
			
			$this->hasMany[$lcf_class_name . 's'] = array('class_name' => $class_name, 'primary_key' => $primary_key, 'foreign_key' => $foreign_key);
		}
		
		public function getHasMany($key)
		{
			$db = Database::getDatabase();
			$tmp_obj = new $this->hasMany[$key]['class_name'];
			$fk = $this->hasMany[$key]['foreign_key'];
			$id = $db->quote($this->{$this->idColumnName});
			$sql = "SELECT * FROM `{$tmp_obj->tableName}` WHERE `$fk` = $id";
			$objs = DBObject::glob($this->hasMany[$key]['class_name'], $sql);
			return $objs;
		}
		
		public function hasAndBelongsToMany($class_name)
		{
			// ?
		}
	}