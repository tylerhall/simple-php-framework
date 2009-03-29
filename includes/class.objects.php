<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends ORMObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('username', 'password', 'level', 'email'), $id);
        }
    }

    class Folder extends ORMObject
    {
        public function __construct($id = null)
        {
            parent::__construct('folders', array('name'), $id);
			$this->hasMany('Document');
        }
    }

    class Document extends ORMObject
    {
        public function __construct($id = null)
        {
            parent::__construct('documents', array('title', 'folder_id'), $id);
			$this->belongsTo('Folder');
        }
    }
