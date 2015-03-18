<?php

include 'config.php';
include 'JsonRpcClient.php';

/*
The method getTransactionID generate a uniq transactionId associated with 
a temporary access rule. This rule define the access as belonging to the 
company account who made the request. You must specify the company username 
(extUserId). This method is available via JSON-RPC 2.0 at the URL:
    http://[host]:[port]/Time4UserServices/services/backend/t4ujson

Input 
    Name: extUserId
	Type: string
	Desc: Id uniq of user in the original Company context (i.e. email)
Output
    Name: transactionId
	Type: string
	Desc: temporary Id to associate the user to the Company
*/

function getTransacionID($username)
{
global $cert_pwd;
global $cert_name;
global $time4user_methods;
	$client = new JsonRpcClient($time4user_methods);
	
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
    Name: UserId
        Type: string
        Desc: Id uniq of user in the original Company context (i.e. email)

    Name: otp
        Type: string
        Desc: one time password to authenticate
*/


function authOTP($username,$otp) 
{	
global $cert_pwd;
global $cert_name;
global $time4id_methods;
	$client = new JsonRpcClient($time4id_methods);
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
