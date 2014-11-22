<?php

import('vanilla.security.crypt.Crypter');

/**
 * 
 */
class Base64URLCrypter implements Crypter
{
    public function encrypt($s)
    {
	return self::encode($s);
    }

    public function decrypt($s)
    {
	return self::decode($s);
    }

//  ---------------------------------------------------------------------->

    public static function encode($s)
    {
	$s = base64_encode($s);
	$s = str_replace("+", "-", $s);
	$s = str_replace("/", "_", $s);

	// on récupère le nombre de = à la fin
	$i = 0;
	while ( substr($s, -1) == '=' )
	{
	    $s = substr($s, 0, -1);
	    $i++;
	}

	return "$s$i";
    }

    public static function decode($s)
    {
	$s = str_replace("-", "+", $s);
	$s = str_replace("_", "/", $s);
	$i = substr($s, -1);
	$s = substr($s, 0, -1);

	while ( $i-- > 0 ) 
	{
	    $s .= "=";
	}

	return base64_decode($s);
    }
}
?>
