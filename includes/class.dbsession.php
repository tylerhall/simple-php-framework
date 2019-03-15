<?PHP
    class DBSession
    {
        public static function register()
        {
			$handler = new DBSession();
            session_set_save_handler(array($handler, 'open'), array($handler, 'close'), array($handler, 'read'), array($handler, 'write'), array($handler, 'destroy'), array($handler, 'gc'));

			// the following prevents unexpected effects when using objects as save handlers
			register_shutdown_function('session_write_close');
			
			session_start();
        }

        public static function open()
        {
            $db = Database::getDatabase();
            return $db->isConnected();
        }

        public static function close()
        {
            return true;
        }

        public static function read($id)
        {
            $db = Database::getDatabase();
            $db->query('SELECT `data` FROM `sessions` WHERE `id` = :id:', array('id' => $id));
            return $db->hasRows() ? $db->getValue() : '';
        }

        public static function write($id, $data)
        {
            $db = Database::getDatabase();
            $db->query('INSERT INTO `sessions` (`id`, `data`, `updated_on`) VALUES (:id:, :data:, :updated_on:) ON DUPLICATE KEY UPDATE `data` = :data:, `updated_on` = :updated_on:', array('id' => $id, 'data' => $data, 'updated_on' => time()));
			return ($db->affectedRows() > 0);
        }

        public static function destroy($id)
        {
            $db = Database::getDatabase();
            $db->query('DELETE FROM `sessions` WHERE `id` = :id:', array('id' => $id));
            return ($db->affectedRows() > 0);
        }

        public static function gc($max)
        {
            $db = Database::getDatabase();
            $db->query('DELETE FROM `sessions` WHERE `updated_on` < :updated_on:', array('updated_on' => time() - $max));
            return true;
        }
    }
