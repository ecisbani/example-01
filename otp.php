<?php 
session_start();
include 'functions.php';
$response='null';
if ($_POST) {
	$otp = $_POST['otp'];   
        if ($otp!='') {
		$response = authOTP($otp);
		if ($response == '') $response = 'ok';
		}
        }
?>

<!DOCTYPE html><html><head> <title>Time4eID DEMO</title> </head>
    
<p>username: <?php echo $_SESSION['username'];?></p>
<p>uniqueTokenId: <?php echo $_SESSION['uniqueTokenId'];?></p>
        <form role="form" name="formProfile" method="post">
	<p>Please, insert the One Time Password generated on your smartphone with the app Valid<br>
        	<input type="text" name="otp" autofocus autocomplete="off" required placeholder="OTP" >
		<button type="submit">Confirm</button>
	        </p>        
	<p>or get the app <a role="button" href="enroll.php">Valid</a> on your smartphone</p>
	<p>or <a href="index.php?logout=true">logout</a> </p>
</form>

<p>response: <?php echo $response; ?></p>

</body> </html>
