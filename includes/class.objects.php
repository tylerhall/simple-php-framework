<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends TaggableDBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', 'id', array('username', 'password', 'level', 'email'), $id);
        }
    }