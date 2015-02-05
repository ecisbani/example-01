<?php
session_start(); 
$error=''; 
if (isset($_POST['submit'])) {
	if (empty($_POST['username']) || ($_POST['password'] != 'aaa')) {
		$error = "Username or Password invalid";
		}
	else {
		$_SESSION['username']=$_POST['username']; 
		}
	}

if ($_GET) {
	if (array_key_exists('uID',$_GET)) { 
		$_SESSION['uniqueTokenId'] = $_GET['uID'];
		}
	if (array_key_exists('logout',$_GET)) {
		session_destroy();
		session_start();
		header('Location: '.$_SERVER['PHP_SELF']);
		}
	}
        
if(isset($_SESSION['username'])){
	if (!array_key_exists('uniqueTokenId',$_SESSION)) {
		$_SESSION['uniqueTokenId'] = 'FA4524AD4C3A49EDEEFA32E9E761BBD390AC178154535D45B14377AE8228058A';
		}
	header("location: otp.php");
	}
?>

<!DOCTYPE html> <html> <head> <title>Login Form in PHP</title> </head> <body>
<h4>PHP Login with Valid 2FA Example</h4>

<form action="" method="post">
	<p><label>Username :</label>
	<input id="name" name="username" placeholder="username" type="text">
	<p><label>Password :</label>
	<input id="password" name="password" placeholder="***" type="password">
	<p><input name="submit" type="submit" value=" Login ">
	<p><?php echo $error; ?></p>
	</form>

</body> </html> 
