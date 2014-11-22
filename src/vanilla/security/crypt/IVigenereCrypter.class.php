<?php

import('vanilla.security.crypt.Crypter');
import('vanilla.security.crypt.Base64URLCrypter');

/**
 * Improved vigenere crypter
 * Se base sur un encodage en base64 pour encoder la chaîne puis la crypt avec
 * un vigenere amélioré.
 */
class IVigenereCrypter implements Crypter
{
    private static $CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private static $CHARS_SIZE = 62;

//  ---------------------------------------------------------------------->

    private $key;

//  ---------------------------------------------------------------------->

    public function __construct($key) 
    {
	$this->key = $key;
    }

//  ---------------------------------------------------------------------->

    public function encrypt($s)
    {
	return self::doEncrypt($s, $this->key);
    }

    public function decrypt($s)
    {
	return self::doDecrypt($s, $this->key);
    }

//  ---------------------------------------------------------------------->

    public static function doEncrypt($s, $key) 
    { 
	$s = Base64URLCrypter::encode($s);
	return self::vigenere($s, $key, true);
    } 

    public static function doDecrypt($s, $key) 
    { 
	$s = self::vigenere($s, $key, false);
	return Base64URLCrypter::decode($s);
    } 

//  ---------------------------------------------------------------------->

    private static function encodeBase64($s)
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

    private static function decodeBase64($s)
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

    private static function vigenere($s, $key, $encode=true)
    {
	$out = "";
	$p = $encode ? 1 : -1;

	$key = str_replace("-", "+", $key);
	$key = str_replace("_", "/", $key);

	$sl = strlen($s);
	$kl = strlen($key);
	for ( $i = 0, $j = 0 ; $i < $sl ; $i++, $j = ++$j%$kl )
	{
	    $c = $s[$i]; 
	    $k = $key[$j];

	    // on laisse les caractères inconnus
	    $v  = strpos(self::$CHARS, $c);
	    if ( $v === false )
	    {
		$out .= $c;
		continue;
	    }

	    $v += $p*max(0, strpos(self::$CHARS, $k));
	    $v += self::$CHARS_SIZE;
	    $v %= self::$CHARS_SIZE;

	    $out .= self::$CHARS[$v];
	}

        return $out;
    }
}
?>
