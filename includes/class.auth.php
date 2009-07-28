<?PHP
    class Auth
    {
        // Singleton object. Leave $me alone.
        private static $me;

        public $id;
        public $username;
        public $level;
        public $user; // DBObject User object (if available)

        private $loggedIn;

        // Call with no arguments to attempt to restore a previous logged in session
        // which then falls back to a guest user (which can then be logged in using
        // $this->login($un, $pw). Or pass a user_id to simply login that user. The
        // $seriously is just a safeguard to be certain you really do want to blindly
        // login a user. Set it to true.
        private function __construct($user_to_impersonate = null)
        {
            $this->id             = null;
            $this->username       = null;
            $this->level          = 'guest';
            $this->user           = null;
            $this->loggedIn       = false;

            if(class_exists('User') && (is_subclass_of('User', 'DBObject')))
                $this->user = new User();

            if(!is_null($user_to_impersonate))
                return $this->impersonate($user_to_impersonate);

            if($this->attemptSessionLogin())
                return;

            if($this->attemptCookieLogin())
                return;
        }

        /**
         * Standard singleton
         * @return Auth
         */
        public static function getAuth($user_to_impersonate = null)
        {
            if(is_null(self::$me))
                self::$me = new Auth($user_to_impersonate);
            return self::$me;
        }

        // You'll typically call this function when a user logs in using
        // a form. Pass in their username and password.
        // Takes a username and a *plain text* password
        public function login($un, $pw)
        {
            $pw = $this->createHashedPassword($pw);
            return $this->attemptLogin($un, $pw);
        }

        public function logout()
        {
            $Config = Config::getConfig();

            $this->id             = null;
            $this->username       = null;
            $this->level          = 'guest';
            $this->user           = null;
            $this->loggedIn       = false;

            if(class_exists('User') && (is_subclass_of('User', 'DBObject')))
                $this->user = new User();

            $_SESSION['un'] = '';
            $_SESSION['pw'] = '';
            setcookie('spf', '.', time() - 3600, '/', $Config->authDomain);
        }

        // Assumes you have already checked for duplicate usernames
        public function changeUsername($new_username)
        {
            $db = Database::getDatabase();
            $db->query('UPDATE users SET username = :username WHERE id = :id', array('username' => $new_username, 'id' => $this->id));
            if($db->affectedRows() == 1)
            {
                $this->impersonate($this->id);
                return true;
            }

            return false;
        }

        public function changePassword($new_password)
        {
            $db = Database::getDatabase();
            $Config = Config::getConfig();

            if($Config->useHashedPasswords === true)
                $new_password = $this->createHashedPassword($new_password);

            $db->query('UPDATE users SET password = :password WHERE id = :id', array('password' => $new_password, 'id' => $this->id));
            if($db->affectedRows() == 1)
            {
                $this->impersonate($this->id);
                return true;
            }

            return false;
        }

        // Is a user logged in? This was broken out into its own function
        // in case extra logic is ever required beyond a simple bool value.
        public function loggedIn()
        {
            return $this->loggedIn;
        }

        // Helper function that redirects away from 'admin only' pages
        public function requireAdmin($url)
        {
            if(!$this->loggedIn() || $this->level != 'admin')
                redirect($url);
        }

        // Helper function that redirects away from 'member only' pages
        public function requireUser($url)
        {
            if(!$this->loggedIn())
                redirect($url);
        }

        // Check if the submitted password matches what we have on file.
        // Takes a *plain text* password
        public function passwordIsCorrect($pw)
        {
            $db = Database::getDatabase();
            $Config = Config::getConfig();

            if($Config->useHashedPasswords === true)
                $pw = $this->createHashedPassword($pw);

            $db->query('SELECT COUNT(*) FROM users WHERE username = :username AND password = BINARY :password', array('username' => $this->username, 'password' => $pw));
            return $db->getValue() == 1;
        }

        // Login a user simply by passing in their username or id. Does
        // not check against a password. Useful for allowing an admin user
        // to temporarily login as a standard user for troubleshooting.
        // Takes an id or username
        public function impersonate($user_to_impersonate)
        {
            $db = Database::getDatabase();
            $Config = Config::getConfig();

            if(ctype_digit($user_to_impersonate))
                $row = $db->getRow('SELECT * FROM users WHERE id = ' . $db->quote($user_to_impersonate));
            else
                $row = $db->getRow('SELECT * FROM users WHERE username = ' . $db->quote($user_to_impersonate));

            if(is_array($row))
            {
                $this->id       = $row['id'];
                $this->username = $row['username'];
                $this->level    = $row['level'];

                // Load any additional user info if DBObject and User are available
                if(class_exists('User') && (is_subclass_of('User', 'DBObject')))
                {
                    $this->user = new User();
                    $this->user->id = $row['id'];
                    $this->user->load($row);
                }

                if($Config->useHashedPasswords === false)
                    $row['password'] = $this->createHashedPassword($row['password']);

                $this->storeSessionData($this->username, $row['password']);
                $this->loggedIn = true;

                return true;
            }

            return false;
        }

        // Attempt to login using data stored in the current session
        private function attemptSessionLogin()
        {
            if(isset($_SESSION['un']) && isset($_SESSION['pw']))
                return $this->attemptLogin($_SESSION['un'], $_SESSION['pw']);
            else
                return false;
        }

        // Attempt to login using data stored in a cookie
        private function attemptCookieLogin()
        {
            if(isset($_COOKIE['spf']) && is_string($_COOKIE['spf']))
            {
                $s = json_decode($_COOKIE['spf'], true);

                if(isset($s['un']) && isset($s['pw']))
                {
                    return $this->attemptLogin($s['un'], $s['pw']);
                }
            }

            return false;
        }

        // The function that actually verifies an attempted login and
        // processes it if successful.
        // Takes a username and a *hashed* password
        private function attemptLogin($un, $pw)
        {
            $db = Database::getDatabase();
            $Config = Config::getConfig();

            // We SELECT * so we can load the full user record into the user DBObject later
            $row = $db->getRow('SELECT * FROM users WHERE username = ' . $db->quote($un));
            if($row === false) return false;

            if($Config->useHashedPasswords === false)
                $row['password'] = $this->createHashedPassword($row['password']);

            if($pw != $row['password']) return false;

            $this->id       = $row['id'];
            $this->username = $row['username'];
            $this->level    = $row['level'];

            // Load any additional user info if DBObject and User are available
            if(class_exists('User') && (is_subclass_of('User', 'DBObject')))
            {
                $this->user = new User();
                $this->user->id = $row['id'];
                $this->user->load($row);
            }

            $this->storeSessionData($un, $pw);
            $this->loggedIn = true;

            return true;
        }

        // Takes a username and a *hashed* password
        private function storeSessionData($un, $pw)
        {
            if(headers_sent()) return false;
            $Config = Config::getConfig();
            $_SESSION['un'] = $un;
            $_SESSION['pw'] = $pw;
            $s = json_encode(array('un' => $un, 'pw' => $pw));
            return setcookie('spf', $s, time()+60*60*24*30, '/', $Config->authDomain);
        }

        private function createHashedPassword($pw)
        {
            $Config = Config::getConfig();
            return sha1($pw . $Config->authSalt);
        }
    }
