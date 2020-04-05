<?PHP
    require 'includes/master.inc.php';

    // Kick out user if already logged in.
    if($Auth->loggedIn()) redirect('index.php');

    // Try to log in...
    if(!empty($_POST['username'])) {
		if(empty($_POST['PIN'])) {
			$Auth->sendTwoStep($_POST['username']);
		} else {
	        $Auth->login($_POST['username'], $_POST['password'], $_POST['PIN']);
	        if($Auth->loggedIn()) {
	            redirect('index.php');
			}
		}
    }

    // Clean the submitted username before redisplaying it.
    $username = isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '';
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title>SPF Sample Login Page</title>
  </head>
  <body>

		<div class="container-fluid">
			<div class="row">
				<div class="col">
					<h2>Login</h2>
					<form action="login.php" method="POST">
						<div class="form-group">
							<label for="username">Username</label>
							<input type="username" class="form-control" name="username" id="username" value="<?PHP echo $username; ?>">
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" class="form-control" name="password" id="password">
						</div>
						<div class="form-group">
							<label for="PIN">PIN</label>
							<input type="text" class="form-control" name="PIN" id="PIN">
						</div>
						<button type="submit" class="btn btn-primary">Login</button>
						<button type="submit" class="btn btn-secondary">Send PIN</button>
					</form>
				</div>
			</div>
		</div>

  </body>
</html>
