<?php

import('vanilla.security.crypt.Crypter');

class Cipher implements Crypter
{
    private $fixed = false;

//  ---------------------------------------------------------------------->

    public function __construct($fixed=false)
    {
	$this->fixed = $fixed;
    }

//  ---------------------------------------------------------------------->

    public function encrypt($s)
    {
	if ( $this->fixed )
	{
	    return self::cipherFixed($s);
	}
	else
	{
	    return self::cipherRandom($s);
	}
    }

    public function decrypt($s)
    {
	return self::decipher($s);
    }

//  ---------------------------------------------------------------------->

    public static function cipherFixed($s)
    {
	return self::doCipher($s, strlen($s) + 1);
    }

    public static function cipherRandom($s)
    {
	return self::doCipher($s, strlen($s) + 1 + rand(0, 5));
    }

    private static function doCipher($str, $len)
    {
	$str = "$str";

	$nbr = strlen($str);
	$rndNbr = $len;
	$table = Array();

	for ( $i = 0 ; $i < $nbr ; $i++ )
	{
	    $table[$i] = ord($str[$i]);
	}

	$table[$nbr] = 0;

	for ( $i = $nbr + 1 ; $i < $rndNbr ; $i++ )
	{
	    $table[$i] = ceil(rand(0, 256)) % 256;
	}

	for ( $k = 0 ; $k < $rndNbr ; $k++ )
	{
	    for ( $i = 0 ; $i < $rndNbr-1 ; $i++ )
	    {
		$table[$i] = ($table[$i] + $i*11 + $i*$i*3 + 13 - $table[$i+1]) % 256;
		if ( $table[$i] < 0 )
		{
		    $table[$i] += 256;
		}
	    }
	}

	for ( $k = 0 ; $k < $rndNbr ; $k++ )
	{
	    for ( $i = $rndNbr-1 ; 0 < $i ; $i--)
	    {
		$table[$i] = ($table[$i] + $i*7 + $i*$i*5 + 17 - $table[$i-1]) % 256;
		if ( $table[$i] < 0 )
		{
		    $table[$i] += 256;
		}
	    }
	}

	$cars = Array();
	for ( $i = 0; $i < $rndNbr; $i++ )
	{
	    $cars[$i*2]   = chr(($table[$i] % 21) + 65);
	    $cars[$i*2+1] = chr(($table[$i] / 21) + 65);
	}

	return join($cars);
    }

    public static function decipher($str)
    {
	$nbr_2 = strlen($str);
	$nbr   = $nbr_2/2;
	$table = Array();

	for ( $i = 0 ; $i < $nbr_2 ; $i++ )
	{
	    $table[$i] = ord($str[$i]);
	}

	$ints = Array();
	for ( $i = 0 ; $i < $nbr; $i++ )
	{
	    $ints[$i] = ($table[$i*2] - 65) + ($table[$i*2+1] - 65)*21;
	}

	for ( $k = 0 ; $k < $nbr ; $k++ )
	{
	    for ( $i = 1 ; $i < $nbr ; $i++ )
	    {
		$ints[$i] = ($ints[$i] + $ints[$i-1] - 17 - $i*$i*5 - $i*7) % 256;
		if ( $ints[$i] < 0 )
		{
		    $ints[$i] = $ints[$i] + 256;
		}
	    }
	}

	for ( $k = 0 ; $k < $nbr ; $k++ )
	{
	    for ( $i = $nbr - 2 ; $i >= 0 ; $i--)
	    {
		$ints[$i] = ($ints[$i] + $ints[$i+1] - 13 - $i*$i*3 - $i*11) % 256;
		if ( $ints[$i] < 0 )
		{
		    $ints[$i] = $ints[$i] + 256;
		}
	    }
	}

	$s = "";
	for ( $i = 0 ; $i < $nbr ; $i++ )
	{
	    if ($ints[$i] == 0) break;
	    $s .= chr($ints[$i]);
	}
	
	return $s;
    }
}
?>
