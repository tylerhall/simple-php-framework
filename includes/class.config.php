<?PHP

    // The Config class provides a single object to store your application's settings.
    // Define your settings as public members. (We've already setup the standard options
    // required for the Database and Auth classes.) Then, assign values to those settings
    // inside the "location" functions. This allows you to have different configuration
    // options depending on the server environment you're running on. Ex: local, staging,
    // and production.

    class Config
    {
        // Singleton object. Leave $me alone.
        private static $me;

        // Add your server hostnames to the appropriate arrays. ($_SERVER['HTTP_HOST'])
        private $productionServers = array();
        private $stagingServers    = array();
        private $localServers      = array('framework.site');

        // Standard Config Options...

        // ...For Auth Class
        public $authDomain;         // Domain to set for the cookie
        public $authSalt;           // Can be any random string of characters
        public $useHashedPasswords; // Store hashed passwords in database? (versus plain-text)

        // ...For Database Class
        public $dbHost;       // Database server
        public $dbName;       // Database name
        public $dbUsername;   // Database username
        public $dbPassword;   // Database password
        public $dbDieOnError; // What do do on a database error (see class.database.php for details)

        // Add your config options here...
        public $useDBSessions; // Set to true to store sessions in the database

        // Singleton constructor
        private function __construct()
        {
            $this->everywhere();

            $i_am_here = $this->whereAmI();

            if('production' == $i_am_here)
                $this->production();
            elseif('staging' == $i_am_here)
                $this->staging();
            elseif('local' == $i_am_here)
                $this->local();
            elseif('shell' == $i_am_here)
                $this->shell();
            else
                die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>class.config.php</code></p>
                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
        }

        /**
         * Standard singleton
         * @return Config
         */
        public static function getConfig()
        {
            if(is_null(self::$me))
                self::$me = new Config();
            return self::$me;
        }

        // Allow access to config settings statically.
        // Ex: Config::get('some_value')
        public static function get($key)
        {
            return self::$me->$key;
        }

        // Add code to be run on all servers
        private function everywhere()
        {
            // Store sesions in the database?
            $this->useDBSessions = true;

            // Settings for the Auth class
            $this->authDomain         = $_SERVER['HTTP_HOST'];
            $this->useHashedPasswords = false;
            $this->authSalt           = ''; // Pick any random string of characters
        }

        // Add code/variables to be run only on production servers
        private function production()
        {
            ini_set('display_errors', '0');

            define('WEB_ROOT', '');

            $this->dbHost       = '';
            $this->dbName       = '';
            $this->dbUsername   = '';
            $this->dbPassword   = '';
            $this->dbDieOnError = false;
        }

        // Add code/variables to be run only on staging servers
        private function staging()
        {
            ini_set('display_errors', '1');
            ini_set('error_reporting', E_ALL);

            define('WEB_ROOT', '');

            $this->dbHost       = '';
            $this->dbName       = '';
            $this->dbUsername   = '';
            $this->dbPassword   = '';
            $this->dbDieOnError = false;
        }

        // Add code/variables to be run only on local (testing) servers
        private function local()
        {
            ini_set('display_errors', '1');
            ini_set('error_reporting', E_ALL);

            define('WEB_ROOT', '');

            $this->dbHost       = '';
            $this->dbName       = '';
            $this->dbUsername   = '';
            $this->dbPassword   = '';
            $this->dbDieOnError = true;
        }

        // Add code/variables to be run only on when script is launched from the shell
        private function shell()
        {
            ini_set('display_errors', '1');
            ini_set('error_reporting', E_ALL);

            define('WEB_ROOT', '');

            $this->dbHost       = '';
            $this->dbName       = '';
            $this->dbUsername   = '';
            $this->dbPassword   = '';
            $this->dbDieOnError = true;
        }

        public function whereAmI()
        {
            if(in_array($_SERVER['HTTP_HOST'], $this->productionServers))
                return 'production';
            elseif(in_array($_SERVER['HTTP_HOST'], $this->stagingServers))
                return 'staging';
            elseif(in_array($_SERVER['HTTP_HOST'], $this->localServers))
                return 'local';
            elseif(isset($_ENV['SHELL']))
                return 'shell';
            else
                return false;
        }
    }
