<?php
session_start(); 

// POST => login submitted
$error='';
if (isset($_POST['submit'])) {
	// fake authentication: any userid valid, only one password is ok
	if (empty($_POST['username']) || ($_POST['password'] != 'Valid!')) {
		$error = "The password you entered isn't Valid!";
		}
	else {
		$_SESSION['username']=$_POST['username']; 
		header("location: otp.php");
		}
	}

// GET => logout from otp.php
if (isset($_GET['logout'])) {
	session_destroy();
	header('Location: '.$_SERVER['PHP_SELF']);
	}
?>

<!DOCTYPE html> <html> <head> <title>Login Form in PHP</title> </head> <body>
<h4>Valid 2FA Simple Integration Example</h4>

<form action="" method="post">
	<p><label>Username :</label>
	<input id="name" name="username" placeholder="username" type="text">
	<p><label>Password :</label>
	<input id="password" name="password" placeholder="***" type="password">
	<p><input name="submit" type="submit" value=" Login ">
	<p><?php echo $error; ?></p>
	</form>

</body> </html> 
