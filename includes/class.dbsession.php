<?PHP
    class DBSession
    {
        public static function register()
        {
            ini_set('session.save_handler', 'user');
            session_set_save_handler(array('DBSession', 'open'), array('DBSession', 'close'), array('DBSession', 'read'), array('DBSession', 'write'), array('DBSession', 'destroy'), array('DBSession', 'gc'));
        }

        public static function open()
        {
            $db = Database::getDatabase(true);
            return $db->isConnected();
        }

        public static function close()
        {
            return true;
        }

        public static function read($id)
        {
            $db = Database::getDatabase(true);
            $db->query('SELECT `data` FROM `sessions` WHERE `id` = :id', array('id' => $id));
            return $db->hasRows() ? $db->getValue() : '';
        }

        public static function write($id, $data)
        {
            $db = Database::getDatabase(true);
            $db->query('DELETE FROM `sessions` WHERE `id` = :id', array('id' => $id));
            $db->query('INSERT INTO `sessions` (`id`, `data`, `updated_on`) VALUES (:id, :data, :updated_on)', array('id' => $id, 'data' => $data, 'updated_on' => time()));
            return ($db->affectedRows() == 1);
        }

        public static function destroy($id)
        {
            $db = Database::getDatabase(true);
            $db->query('DELETE FROM `sessions` WHERE `id` = :id', array('id' => $id));
            return ($db->affectedRows() == 1);
        }

        public static function gc($max)
        {
            $db = Database::getDatabase(true);
            $db->query('DELETE FROM `sessions` WHERE `updated_on` < :updated_on', array('updated_on' => time() - $max));
            return true;
        }
    }