<?php

import('vanilla.security.crypt.CrypterChain');
import('vanilla.security.crypt.IVigenereCrypter');
import('vanilla.security.crypt.Cipher');

class CaptchaHelper
{
    private static $CHARS = 'abcdefghkmnopqrstuvwxyz';

//  ----------------------------------------------->

    private static $crypter = null;

//  ----------------------------------------------->

    /**
     * Génère un code captcha de la taille length puis l'encode.
     * Ce code pourra être lu par la méthode decedeCaptch
     */
    public static function generateEncodedCaptcha($length)
    {
	$captcha = self::generateCaptcha($length);
	return self::getCrypter()->encrypt($captcha);
    }

    private static function generateCaptcha($length)
    {
	$chars = self::$CHARS;

	$captcha = "";
	$l = strlen(self::$CHARS) - 1;
	for ( $i = 0 ; $i < $length ; $i++ )
	{
	    $l = strlen($chars) - 1;
	    $c = rand(0, $l);
	    $captcha .= $chars[$c];

	    $chars = ($c > 0 ? substr($chars, 0, $c-1) : "") . ($c < $l ? substr($chars, $c+1) : "");
	    if ( empty($chars) )
	    {
		$chars = self::$CHARS;
	    }
	}

	return $captcha;
    }

//  ----------------------------------------------->

    /**
     * Spécification d'un crypter pour encrypter la chaîne de caractères
     */
    public static function setCrypter(Crypter $crypter)
    {
	self::$crypter = $crypter;
    }

    public static function getCrypter()
    {
	if ( empty(self::$crypter) )
	{
	    /*
	       On en définie un par défaut
	    */

	    $pwd = defined('CAPTCHA_PASSWORD') ? CAPTCHA_PASSWORD : md5(__FILE__);

	    self::$crypter = new CrypterChain();
	    self::$crypter->append(new IVigenereCrypter($pwd))->append(new Cipher());
	}

	return self::$crypter;
    }

//  ----------------------------------------------->

    public static function decodeCaptcha($encodedCaptcha)
    {
	return self::getCrypter()->decrypt($encodedCaptcha);
    }

    public static function checkCaptcha($encodedCaptcha, $string)
    {
	return strtolower(self::decodeCaptcha($encodedCaptcha)) == strtolower($string);
    }
}
?>
