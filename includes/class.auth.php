<?PHP
    class Auth
    {
        private static $me;

        public $id;
        public $username;
        public $user;
        public $expiryDate;
        public $loginUrl;

        private $nid;
        private $loggedIn;

        public function __construct()
        {
            $this->id         = null;
            $this->nid        = null;
            $this->username   = null;
            $this->user       = null;
            $this->loggedIn   = false;
            $this->expiryDate = mktime(0, 0, 0, 6, 2, 2037);
            $this->user       = new User();
            $this->loginUrl   = WEB_ROOT . 'login.php';
        }

        public static function getAuth()
        {
            if(is_null(self::$me))
                self::$me = new Auth();
            return self::$me;
        }

        public function init()
        {
            $this->setACookie();
            $this->loggedIn = $this->attemptCookieLogin();
        }

        public function login($username, $password)
        {
            $this->loggedIn = false;

            $db = Database::getDatabase();
            $hashed_password = self::hashedPassword($password);
            $row = $db->getRow("SELECT * FROM users WHERE username = " . $db->quote($username) . " AND password = " . $db->quote($hashed_password));

            if($row === false)
                return false;

            $this->id       = $row['id'];
            $this->nid      = $row['nid'];
            $this->username = $row['username'];
            $this->user     = new User();
            $this->user->id = $this->id;
            $this->user->load($row);

            $this->generateBCCookies();

            $this->loggedIn = true;

            return true;
        }

        public function logout()
        {
            $this->loggedIn = false;
            $this->clearCookies();
            $this->sendToLoginPage();
        }

        public function loggedIn()
        {
            return $this->loggedIn;
        }

        public function requireUser()
        {
            if(!$this->loggedIn())
                $this->sendToLoginPage();
        }

        public function requireAdmin()
        {
            if(!$this->loggedIn() || !$this->isAdmin())
                $this->sendToLoginPage();
        }

        public function isAdmin()
        {
            return ($this->user->level === 'admin');
        }

        public function changeCurrentUsername($new_username)
        {
            $db = Database::getDatabase();
            srand(time());
            $this->user->nid = Auth::newNid();
            $this->user->username = $new_username;
            $this->user->update();
            $this->username = $this->user->username;
            $this->nid = $this->user->nid;
            $this->generateBCCookies();
        }

        public function changeCurrentPassword($new_password)
        {
            $db = Database::getDatabase();
            srand(time());
            $this->user->nid = self::newNid();
            $this->user->password = self::hashedPassword($new_password);
            $this->user->update();
            $this->nid = $this->user->nid;
            $this->generateBCCookies();
        }

        public static function changeUsername($id_or_username, $new_username)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if($u->ok())
            {
                $u->username = $new_username;
                $u->update();
            }
        }

        public static function changePassword($id_or_username, $new_password)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if($u->ok())
            {
                $u->nid = self::newNid();
                $u->password = self::hashedPassword($new_password);
                $u->update();
            }
        }

        public static function createNewUser($username, $password)
        {
            srand(time());
            $u = new User();
            $u->username = $username;
            $u->nid = self::newNid();
            $u->password = self::hashedPassword($password);
            $u->insert();
            return $u;
        }

        // Generates a strong password of default length 9 characters.
        // Contains at least one symbol and one number.
        // The available characters have been chosen for legibility reasons.
        // This prevents users from being confused by things like 'l' versus '1'
        // and 'O' versus '0', etc.
        public static function generateStrongPassword($length = 9)
        {
            $all = str_split('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%&*');
            $symbols = str_split('!@#$%&*');
            $digits = str_split('23456789');

            $password = '';
            for($i = 0; $i < $length - 2; $i++)
                $password .= $all[array_rand($all)];

            $password .= $symbols[array_rand($symbols)];
            $password .= $digits[array_rand($digits)];

            return str_shuffle($password);
        }

        public function impersonateUser($id_or_username)
        {
            if(ctype_digit($id_or_username))
                $u = new User($id_or_username);
            else
            {
                $u = new User();
                $u->select($id_or_username, 'username');
            }

            if(!$u->ok()) return false;

            $this->id       = $u->id;
            $this->nid      = $u->nid;
            $this->username = $u->username;
            $this->user     = $u;
            $this->generateBCCookies();

            return true;
        }

        private function attemptCookieLogin()
        {
            if(!isset($_COOKIE['A']) || !isset($_COOKIE['B']) || !isset($_COOKIE['C']))
                return false;

            $ccookie = base64_decode(str_rot13($_COOKIE['C']));
            if($ccookie === false)
                return false;

            $c = array();
            parse_str($ccookie, $c);
            if(!isset($c['n']) || !isset($c['l']))
                return false;

            $bcookie = base64_decode(str_rot13($_COOKIE['B']));
            if($bcookie === false)
                return false;

            $b = array();
            parse_str($bcookie, $b);
            if(!isset($b['s']) || !isset($b['x']))
                return false;

            if($b['x'] < time())
                return false;

            $computed_sig = md5(str_rot13(base64_encode($ccookie)) . $b['x'] . Config::get('authSalt'));
            if($computed_sig != $b['s'])
                return false;

            $nid = base64_decode($c['n']);
            if($nid === false)
                return false;

            $db = Database::getDatabase();

            // We SELECT * so we can load the full user record into the DBObject later
            $row = $db->getRow('SELECT * FROM users WHERE nid = ' . $db->quote($nid));
            if($row === false)
                return false;

            $this->id       = $row['id'];
            $this->nid      = $row['nid'];
            $this->username = $row['username'];
            $this->user     = new User();
            $this->user->id = $this->id;
            $this->user->load($row);

            return true;
        }

        private function setACookie()
        {
            if(!isset($_COOKIE['A']))
            {
                srand(time());
                $a = md5(rand() . microtime());
                setcookie('A', $a, $this->expiryDate, '/', Config::get('authDomain'));
            }
        }

        private function generateBCCookies()
        {
            $c  = '';
            $c .= 'n=' . base64_encode($this->nid) . '&';
            $c .= 'l=' . str_rot13($this->username) . '&';
            $c = base64_encode($c);
            $c = str_rot13($c);

            $sig = md5($c . $this->expiryDate . Config::get('authSalt'));
            $b = "x={$this->expiryDate}&s=$sig";
            $b = base64_encode($b);
            $b = str_rot13($b);

            setcookie('B', $b, $this->expiryDate, '/', Config::get('authDomain'));
            setcookie('C', $c, $this->expiryDate, '/', Config::get('authDomain'));
        }

        private function clearCookies()
        {
            setcookie('B', '', time() - 3600, '/', Config::get('authDomain'));
            setcookie('C', '', time() - 3600, '/', Config::get('authDomain'));
        }

        private function sendToLoginPage()
        {
            $url = $this->loginUrl;

            $full_url = full_url();
            if(strpos($full_url, 'logout') === false)
            {
                $url .= '?r=' . $full_url;
            }

            redirect($url);
        }

        private static function hashedPassword($password)
        {
            return md5($password . Config::get('authSalt'));
        }

        private static function newNid()
        {
            srand(time());
            return md5(rand() . microtime());
        }
    }
