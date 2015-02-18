<?php 
session_start();
include 'functions.php';
$response='none';

// check if authenticated with password
if (!isset($_SESSION['username'])) {
	session_destroy();
	header("location: index.php");
	}
// ########## togiere quando funzionerà authenticateByUser
if (!isset($_COOKIE['uniqueTokenId'])) {
	header("location: enroll.php");
	}

// POST => authenticate with OTP
if ($_POST) {
	$otp = $_POST['otp'];   
        if ($otp!='') {
		// ########## sostituire quando funzionerà authenticateByUser
		$response = authOTP($_COOKIE['uniqueTokenId'],$otp);
		//$response = authOTP($_SESSION['username',$otp);
		if ($response == '') $response = 'ok';
		}
        }

// ############### togliere quando funzionerà authenticateByUser
if (isset($_GET['uID'])) { 
	setcookie('uniqueTokenId', $_GET['uID'], time() + (86400 * 365), "/");
        header("location: otp.php");
        }
?>

<!DOCTYPE html><html><head> <title>Time4eID DEMO</title> </head>
<p>username: <?php echo $_SESSION['username'];?></p>
<!-- ############### togliere quando funzionerà authenticateByUser -->
<p>uniqueTokenId: <?php echo $_COOKIE['uniqueTokenId'];?></p>
<p>Please, insert the One Time Password generated on your smartphone with the app Valid
<form role="form" name="formProfile" method="post">
       	<input type="text" name="otp" autofocus autocomplete="off" required placeholder="OTP" >
	<button type="submit">Confirm</button>
	[ response: <b><?php echo $response; ?></b> ]
</form>
<p>or get <a role="button" href="enroll.php">Valid</a> on your smartphone
<p>or <a href="index.php?logout=true">logout</a> </p>
</body> </html>
