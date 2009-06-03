<?PHP
    require 'includes/master.inc.php';

    // Kick out user if already logged in.
    if($Auth->loggedIn()) redirect('index.php');

    // Try to log in...
    if(!empty($_POST['username']))
    {
        $Auth->login($_POST['username'], $_POST['password']);
        if($Auth->loggedIn())
            redirect('index.php');
        else
            $Error->add('username', "We're sorry, you have entered an incorrect username and password. Please try again.");
    }

    // Clean the submitted username before redisplaying it.
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Simple PHP Framework</title>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css">
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css">
    <link rel="stylesheet" href="styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
</head>

<body>
    <h1>Simple PHP Framework</h1>
    <div id="main">
        <h2>Sample Login Form</h2>
        <form action="login.php" method="post">
            <p>This is a sample login form that demonstrates how to use the <code>Auth</code> class to login a user.</p>
            <?PHP echo $Error; ?>
            <p><label for="username">Username:</label> <input type="text" name="username" value="<?PHP echo $username;?>" id="username" /></p>
            <p><label for="password">Password:</label> <input type="password" name="password" value="" id="password" /></p>
            <p><input type="submit" name="btnlogin" value="Login" id="btnlogin" /></p>
        </form>
    </div>
</body>
</html>