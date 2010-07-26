<?PHP
    class Tag extends DBObject
    {
        public function __construct($id = '')
        {
            parent::__construct('tags', array('name'), '');
            $this->select($id, 'name');
            if(!$this->ok())
            {
                $this->name = $id;
                $this->insert();
            }
        }
    }
