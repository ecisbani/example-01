<?php
session_start();
include 'functions.php';

$_SESSION['TransactionID'] = getTransacionID();    

if ($_SESSION['TransactionID']=='') {
	header("Location: ".$_SERVER['HTTP_REFERER'].'?msg=1000');
	}
else {
	header("Location: ".$_SESSION['WIDGET']."serv/getCompanyServiceInfo.php?tid=".$_SESSION['TransactionID']."&extUID=".$_SESSION['username']);
}
?>
