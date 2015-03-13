<?php 
session_start();
include 'functions.php';
$response='none';

// check if not authenticated with password
if (!isset($_SESSION['username'])) {
	session_destroy();
	header("location: index.php");
	}

// POST => authenticate with OTP
if ($_POST) {
	$otp = $_POST['otp'];   
        if ($otp!='') {
		$response = authOTP($_SESSION['username'],$otp);
		if ($response == '') $response = 'ok';
	/* You should manage some bad responses, most relevant cases are:
       		1) 808 = the user entered a bad OTP
	        2) 816 = user entered 3 bad OTP in a row, 
		         use the App to resync
		3) 809 = token is locked, unlock it from admin control
		         panel (https://admin.time4mind.com)
	        4) -4002 = no token found for this user,
			   her should go to the enroll process 
	*/
		}
        } 
else {
// GET => enroll completed
if (isset($_GET['uID'])) {
	$response = $_GET['uID'];
	}
if (isset($_GET['msg'])) {
	//$response = $_GET['msg'];
	$response = $_GET;
/* You should manage responses, most relevant cases are:
 257 = The service is not available or authorized (TransactionID is empty)
 401 = An unexpected error occurred on the server, retry later
 402 = Service data are not available, retry later
 403 = The company user has already activated the service with a different Time4Mind credentials
 404 = The Time4Mind user has already activated the service with a different company username
 405 = The service has reached the maximum number of users allowed
       (you can prevent this usually but concurrent enrollements may occur...)
 406 = SESSION EXPIRED OR NOT VALID
 515 = Time at your disposal is expired! (TransactionID expired)
 516 = TRANSACTION ID IS NOT VALID
1000 = The service is not available or authorized (TransactionID is empty)
1001 = Well done! The service is now enabled
*/
	}
}
?>

<!DOCTYPE html><html><head> <title>Time4eID DEMO</title> </head>
<p>username: <?php print_r ($_SESSION['username']);?></p>
<p>Please, insert the One Time Password generated on your smartphone with the app Valid
<form role="form" name="formProfile" method="post">
       	<input type="text" name="otp" autofocus autocomplete="off" required placeholder="OTP" >
	<button type="submit">Confirm</button>
</form>
<p>[ response: <b><?php print_r ($response); ?></b> ]
<p>or get <a role="button" href="enroll.php">Valid</a> on your smartphone
<p>or <a href="index.php?logout=true">logout</a> </p>
</body> </html>
