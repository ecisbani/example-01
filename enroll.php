<?php
session_start();
include 'functions.php';

// associate a TransactionID to the username
$_SESSION['TransactionID'] = getTransacionID($_SESSION['username']);    

// redirect the browser to the Valid pages to complete the enroll procedure
if ($_SESSION['TransactionID']!='') {
	header("Location: ".$_SESSION['WIDGET']."?tid=".$_SESSION['TransactionID']."&extUID=".$_SESSION['username']);
}
?>

