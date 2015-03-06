<?php

include 'config.php';
include 'JsonRpcClient.php';

/*
This method generate a uniq transactionId associated with a temporary 
access rule. This rule define the access as belonging to the company 
account who made the request. You should specify the company username 
(extUserId) to strengthen the user authentication.
This method is available via JSON-RPC 2.0 at the URL:
http://[host]:[port]/Time4UserServices/services/backend/t4ujson

Input 
	Nome: extUserId
	Tipo: string
	Req : optional
	Desc: Id uniq of user in the original Company context (i.e. email)
Output
	Nome: transactionId
	Tipo: string
	Desc: temporary Id to associate the user to the Company
*/

function getTransacionID($username)
{
global $cert_pwd;
global $cert_name;
global $sme_host;
	$client = new JsonRpcClient($sme_host.'/Time4UserServices/services/backend/t4ujson');
	
	$client->sslCheck(false);
	$client->sslClientAuth($cert_name, $cert_pwd);
	$client->debug = false;
	$param = array('extUserId' => $username);
	$result = $client->getTransactionId($param);

	return $result['transactionId'];
}

/*
This method authenticateByUser verify a OTP associated to a given company username (extUserId). 
This method is available via JSON-RPC 2.0 at the URL:
http://[host]:[port]/Time4eID/backend/auth

Input 
        Nome: extUserId
        Tipo: string
        Req : mandatory
        Desc: Id uniq of user in the original Company context (i.e. email)
Output
        Nome: transactionId
        Tipo: string
        Desc: temporary Id to associate the user to the Company
*/


function authOTP($username,$otp) 
{	
global $cert_pwd;
global $cert_name;
global $sme_host;
	$client = new JsonRpcClient($sme_host.'/Time4eID/backend/auth');
	$client->debug = false;
	$client->sslCheck(false);
	$client->sslClientAuth($cert_name, $cert_pwd);
	
	try {
		$param = array( 'userId' => $username,
				'otp' => $otp);
		$result = $client->authenticateByUser($param);
		}
	catch (JsonRpcFault $e) {
		$result = $e->getCode()." (".$e->getMessage().")" ;
                }
	return $result;
}

?>
