<?PHP
    // Just a sample script demonstrating how to use the Auth class to logout a user.

    require 'includes/master.inc.php';
    $Auth->logout();
    redirect('index.php');