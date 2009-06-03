<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Simple PHP Framework</title>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css" />
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css" />
    <link rel="stylesheet" href="styles/screen.css" type="text/css" media="screen" title="Screen" charset="utf-8" />
</head>

<body>
    <h1>Simple PHP Framework</h1>
    <div id="main">
        <h2>Welcome to the Simple PHP Framework</h2>
        <p><abbr title="The Simple PHP Framework">SPF</abbr> is a pragmatic approach to building websites with PHP 5. It's geared towards web design shops and freelance programmers looking for a common foundation to quickly bring web projects to life. Without getting too technical, SPF follows the <em><a href="http://toys.lerdorf.com/archives/38-The-no-framework-PHP-MVC-framework.html">no-framework Framework</a></em> method coined by Rasmus Lerdorf &mdash; with a little <a href="http://en.wikipedia.org/wiki/Active_record_pattern">Active Record</a> thrown in for good measure.</p>

        <h2>So now what?</h2>
        <p>What you're seeing here is just the default <code>index.php</code> page. If you know what you're doing, feel free to delete it and do your thing. Otherwise keep reading.</p>

        <h3>First, let's make sure everything is setup correctly...</h3>
        <ul>
            <?PHP if(phpversion() >= '5.2.0') : ?>
            <li>Awesome. You're running PHP <?PHP echo phpversion(); ?>. That'll work great.</li>
            <?PHP else: ?>
            <li class="error">Uh oh. You're running PHP version <?PHP echo phpversion(); ?>. The Framework requires version 5.2.0 or greater.</li>
            <?PHP endif; ?>
        </ul>

        <h3>Next, import the database schema...</h3>
        <p>You'll probably want to import our database schema. That will create the <code>users</code> and <code>sessions</code> tables. Here's <a href="_masters/mysql.sql">the schema</a>. Use your favorite MySQL tool or command line to import it.</p>

        <h3>And that's it!</h3>
        <p>You're done. There's nothing else to setup.</p>
        <p>If you come across any bugs or have a feature request, you can <a href="http://code.google.com/p/simple-php-framework/issues/entry">create a new issue report</a> in our <a href="http://code.google.com/p/simple-php-framework/">Google Code project</a>. If have any questions or would just like to discuss the Framework with other developers, post a message in our <a href="http://groups.google.com/group/simple-php-framework">Google Group</a>.</p>

        <p class="legal">The Simple PHP Framework is copyright &copy; 2006 - 2008 <a href="http://clickontyler.com">Tyler Hall</a> and is released under the <a href="http://www.opensource.org/licenses/mit-license.php">MIT Open Source License</a>. That said, it is hardly a one-person project. Many people have submitted bugs, code, and offered their advice freely. Their support is greatly appreciated.</p>
    </div>
</body>
</html>