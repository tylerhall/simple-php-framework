<?PHP
    // Application flag
    define('SPF', true);

    // https://twitter.com/#!/marcoarment/status/59089853433921537
    date_default_timezone_set('UTC');

    // Determine our absolute document root
    define('DOC_ROOT', realpath(dirname(__FILE__) . '/../'));

    // Global include files
    require DOC_ROOT . '/includes/functions.inc.php';
    require DOC_ROOT . '/includes/class.dbobject.php'; // DBOBject...
    require DOC_ROOT . '/includes/class.objects.php';  // and its subclasses

	spl_autoload_register('spf_autoload');

    // Load our config settings
    $Config = Config::getConfig();

    // Store session info in the database?
    if(Config::get('useDBSessions') === true) {
        DBSession::register();
	}

    // Initialize our session
    session_name('spfs');
    session_start();

    // Initialize current user
    $Auth = Auth::getAuth();

    // Object for tracking and displaying error messages
    $Error = SPFError::getError();

    // If you need to bootstrap a first user into the database, you can run this line once
    // Auth::createNewUser('username', 'password', 'level');
