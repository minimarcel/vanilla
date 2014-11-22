<?php

import('vanilla.mail.InternetAddressException');
import('vanilla.mail.MimeUtil');

class InternetAddress
{
    private $address;
    private $personal;
    private $encodedPersonal;

// -------------------------------------------------------------------------->

    public function __construct($address, $personal=null, $charset=null)
    {
	$this->setAddress($address);
	$this->setPersonal($personal, $charset);
    }

// -------------------------------------------------------------------------->

    public function setAddress($address)
    {
	$address = trim($address);
	self::checkAddress($address);
	$this->address = $address;
    }

    public function setPersonal($personal, $charset=null)
    {
	$this->personal = trim($personal);
	if ( empty($this->personal) )
	{
	    $this->encodedPersonal = null;
	}
	else
	{
	    $this->encodedPersonal = MimeUtil::encodeWord($personal, $charset);
	}
    }

// -------------------------------------------------------------------------->

    public function getType()
    {
	return "rfc822";
    }

    public function getAddress()
    {
	return $this->address;
    }

    public function getPersonal()
    {
	return $this->personal;
    }

    public function getLine($gotPersonal=true)
    {
	$s = "";
	if ( $gotPersonal && !empty($this->encodedPersonal) )
	{
	    $s = MimeUtil::quote($this->encodedPersonal, MimeUtil::MIME_DELIMITERS) . " ";
	}

	return "$s<" . $this->address . ">";
    }

    public function __toString()
    {
	return $this->getLine(true);
    }

// -------------------------------------------------------------------------->

    public static function checkAddress($s)
    {
	if ( empty($s) )
	{
	    throw new InternetAddressException('Invalid address : null');
	}

	$index;
                
	$localName = null;
	$domain    = null;
	
	if( ($index = strpos($s, '@')) >= 0) 
	{
	    if ( $index == 0 )
	    {
		throw new InternetAddressException("Missing local name", $s, $index);
	    }
	    
	    if ( $index == (strlen($s) - 1) )
	    {
		throw new InternetAddressException("Missing domain", $s, $index);
	    }
	    
	    $localName = substr($s, 0, $index);
	    $domain = substr($s, $index + 1);
	}
	else 
	{
	    throw new InternetAddressException("Illegal address", $s, -1);
	}
	
	if( ($index = self::indexOfAny($s, " \t\n\r")) >= 0)
	{
	    throw new InternetAddressException("Illegal whitespace in address", $s, $index);
	}
	
	if( ($index = self::indexOfAny($localName, "()<>,;:\\\"[]@")) >= 0 )
	{
	    throw new InternetAddressException("Illegal character in local name", $s, $index);
	}

	if ( !empty($domain) && ($index = strpos($domain, '.')) === false )   
	{
	    throw new InternetAddressException("Illegal domain", $s, strlen($localName) + $index);
	}

	if( !empty($domain) && ($index = self::indexOfAny($domain, "()<>,;:\\\"[]@")) >= 0 )
	{
	    throw new InternetAddressException("Illegal character in domain", $s, strlen($localName) + $index);
	}
    }

    private static function indexOfAny($s, $toSearch, $index=0)
    {
	for ( $i = $index; $i < strlen($s); $i++ )
	{
	    if ( strpos($toSearch, $s[$i]) !== false )
	    {
		return $i;
	    }
	}
	return -1;
    }

// -------------------------------------------------------------------------->

    public static function getLocal()
    {
	// TODO
	return null;
    }

    public static function create($address, $personal=null, $charset=null)
    {
	$a = new InternetAddress($address, $personal, $charset=null);
	return $a;
    }

    public static function toAddressLine($addresses, $lineLength=0, $gotPersonal=true)
    {
	if ( empty($addresses) )
	{
	    return null;
	}

	if ( !is_array($addresses) )
	{
	    $addresses = array($addresses);
	}

	$s = "";
	foreach ( $addresses as $address )
	{
	    if ( !empty($s) )
	    {
		$s = "$s, ";
		$lineLength += 2;
	    }

	    $a = $address->getLine($gotPersonal);

	    $l = self::lengthOfFirstSegment($s);
	    if( ($lineLength + $l) > 76)
	    {
		$s = "$s\r\n\t";
		$lineLength = 8;
	    }

	    $s = "$s$a";

	    $lineLength = self::lengthOfLastSegment($a, $lineLength);
	}

	return $s;
    }

    private static function lengthOfFirstSegment($s)
    {
	if ( ($i = strpos($s, "\r\n")) !== null )
	{
	    return $i;
	}

	return strlen($s);
    }

    private static function lengthOfLastSegment($s, $length)
    {
	if ( ($i = strrpos($s, "\r\n")) !== null )
	{
	    return strlen($s) - $i - 2;
	}

	return strlen($s) + $length;
    }
}
?>
