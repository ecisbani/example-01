<?php

include 'config.php';
include 'JsonRpcClient.php';

/*
Il metodo getTransactionId consente di generare un transactionId univoco associato ad una regola temporanea di accesso. Tale regola definisce l'attestazione di un accesso sul nodo relativo all'utente amministrazione che richiede il transactionId invocando il metodo in questione.
Il metodo consente, in modo opzionale, di specificare all'interno della regola di accesso un identificativo esterno (extUserId) per rafforzare la fase di autenticazione dell'utente. Il metodo è accessibile via JSON-RPC 2.0 al seguente URL:
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
global $t4u_host;
	$client = new JsonRpcClient($t4u_host.'/Time4UserServices/services/backend/t4ujson');
	
	$client->sslCheck(false);
	$client->sslClientAuth($cert_name, $cert_pwd);
	$client->debug = false;
	$param = array('extUserId' => $username);
	$result = $client->getTransactionId($param);

	return $result['transactionId'];
}

/*
Il metodo authenticateByUser consente di verificare una OTP associata ad un identificativo esterno (extUserId). Il metodo è accessibile via JSON-RPC 2.0 al seguente URL:
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
global $t4id_host;
	$client = new JsonRpcClient($t4id_host.'/Time4eID/backend/auth');
	$client->debug = false;
	$client->sslCheck(false);
	$client->sslClientAuth($cert_name, $cert_pwd);
	
	try {
		// ########## sostituire quando funzionerà authenticateByUser
		// anche la chiamata in otp.php
		/*$param = array( 'userId' => $username],
				'otp' => $otp);
		$result = $client->authenticateByUser($param);*/
		$param = array( 'uniqueTokenId' => $username,
				'otp' => $otp);
		$result = $client->authenticate($param); 
		}
	catch (JsonRpcFault $e) {
		$result = $e->getCode()." (".$e->getMessage().")" ;
                }
	return $result;
}


// check if the user need to enroll
function enroll($user)
{
global $cert_pwd;
global $cert_name;
global $hostname;
	$client = new JsonRpcClient($hostname.'/Time4eID/backend/auth');
	
	$client->sslCheck(false);
	$client->sslClientAuth($cert_name, $cert_pwd);
	$client->debug = false;
	$param = array('userId' => $user);
	$result = $client->listToken($param);
	return $result; 

}

?>
