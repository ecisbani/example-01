<?php
session_start();
include 'functions.php';

// associate a TransactionID to the username
$_SESSION['TransactionID'] = getTransacionID($_SESSION['username']);    
/* You should manage some bad responses, most relevant cases are:
257: param externaUserId can not be empty
768: error when try to load data from database
17154: user in not attested to any node
17155: node authorization failed
17664: service non found on nodeId where user is attested
*/

// redirect the browser to the Valid pages to complete the enroll procedure
if ($_SESSION['TransactionID']!='') {
	header("Location: ".$_SESSION['WIDGET']."?tid=".$_SESSION['TransactionID']."&extUID=".$_SESSION['username']);
}
?>

