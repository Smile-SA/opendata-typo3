<?php
/*
 * $Id: randomGenerator.php 48432 2011-06-07 09:27:20Z emilieprudhomme $
 */
/**
 * Generates a random password
 *
 * @param	integer		$passwordSize: length of password
 * @return	string,		the password
 */
function pwdGenerator( $passwordSize = 10) {

	//-- list of possible characters in the password
	$charList = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	//-- initializes the generator of random values
	mt_srand((double)microtime()*1000000);
	$password="";
	while( strlen( $password )< $passwordSize ) {
		$password .= $charList[mt_rand(0, strlen($charList)-1)];
	}
	return $password;
}
?>