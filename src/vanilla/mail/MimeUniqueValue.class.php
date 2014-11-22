<?php

import('vanilla.mail.InternetAddress');

class MimeUniqueValue
{
    private static $part    = 0;
    private static $id	    = 0;
    
// -------------------------------------------------------------------------->

    public static function getUniqueBoundaryValue()
    {
	$s = "----=_Part_". (self::$part++) ."_";
	$s = $s . md5($s) . '.' . time();

	return $s;
    }

    public static function getUniqueMessageId($from=null)
    {
	$local = $from;
	if ( empty($local) )
	{
	    // try to get the local address
	    $local = InternetAddress::getLocal();
	}

	$localString = (!empty($local) ? $local->getAddress() : "mailuser@localhost");

	$s = (self::$id++);
	$s = $s . md5($s) . '.' . time() . "VanillaMailer.$localString";

	return $s;
     }
}
?>
