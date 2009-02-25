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

        // Add your server names to the appropriate arrays.
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

            if(in_array($_SERVER['HTTP_HOST'], $this->productionServers))
                $this->production();
            elseif(in_array($_SERVER['HTTP_HOST'], $this->stagingServers))
                $this->staging();
            elseif(in_array($_SERVER['HTTP_HOST'], $this->localServers))
                $this->local();
            else
                die('<h1>Where am I?</h1> <p>You need to setup your server names in <code>class.config.php</code></p>
                     <p><code>$_SERVER[\'HTTP_HOST\']</code> reported <code>' . $_SERVER['HTTP_HOST'] . '</code></p>');
        }

        // Get Singleton object
        public static function getConfig()
        {
            if(is_null(self::$me))
                self::$me = new Config();
            return self::$me;
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

            $this->dbHost       = 'localhost';
            $this->dbName       = 'framework15';
            $this->dbUsername   = 'root';
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
            else
                return false;
        }
    }