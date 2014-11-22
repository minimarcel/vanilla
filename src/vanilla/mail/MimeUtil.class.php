<?php

import('vanilla.mail.DataHandler');
import('vanilla.io.StringWriter');
import('vanilla.io.QuotedPrintableWriter');
import('vanilla.io.Base64EncoderWriter');
import('vanilla.mail.MimeQEncoder');
import('vanilla.mail.MimeBEncoder');

class MimeUtil
{
    const ASCII             = 1;    // la chaîne contient que des caratères ASCII
    const ASCII_NO_ASCII    = 2;    // la chaîne contient plus de caratères ASCII que de caractères non ASCII
    const NO_ASCII          = 3;    // la chaîne contient plus de caractères non ASCII

    const RFC822_DELIMITERS = "()<>@,;:\\\"\t .[]";
    const MIME_DELIMITERS   = "()<>@,;:\\\"\t []/?=";

    const Q_ENCODER_PREFIX  = "Q";
    const B_ENCODER_PREFIX  = "B";

    public static $DefaultCharset   = 'UTF-8';

// -------------------------------------------------------------------------->
    
    public static function getEncoding(DataHandler $handler)
    {
	$writer = new StringWriter();
	$handler->writeTo($writer);
	$s = $writer->getBuffer();

	$type = $handler->getContentType();
	if ( $type->match('text', '*') )
	{
	    $ascii = self::checkASCII($s, false);

	    switch($ascii)
	    {
		case self::ASCII	    : return "7bit";
		case self::ASCII_NO_ASCII   : return "quoted-printable";
		case self::NO_ASCII	    : return "8bit";
	    }

	}
	else
	{
	    $ascii = self::checkASCII($s, true);
	    switch($ascii)
	    {
		case self::ASCII	    : return "7bit";
		default			    : return "base64";
	    }
	}

	return null;
    }

// -------------------------------------------------------------------------->

    /**
     * Retourne le writer capable d'encoder le contenu suivant le paramètre encoding
     */
    public static function getWriterToEncode(Writer $writer, $encoding)
    {
	if ( empty($encoding) )
	{
	    return $writer;
	}

	$encoding = strtolower($encoding);
	if ( $encoding === "binary" || $encoding === "8bit" || $encoding === "7bit" )
	{
	    return $writer;
	}

	if ( $encoding === "quoted-printable" )
	{
	    $writer = new QuotedPrintableWriter($writer, 76);
	    return $writer;
	}
	else if ( $encoding == "base64" ) 
	{
	    $writer = new Base64EncoderWriter($writer, 76);
	    return $writer;
	}

	throw new MimeException("Unkwown encoding $encoding");	
    }

// -------------------------------------------------------------------------->

    /**
     * Détermine si la chaîne donnée contient que des caractères ASCII, 
     * une majorité de caractères ASCII, ou une majorité de caractère non ASCII
     */
    public static function checkASCII($s, $stopOnFirstNonASCII=false)
    {
	$nbASCII    = 0;
	$nbNonASCII = 0;

	$len = strlen($s);
	for ( $i = 0 ; $i < $len ; $i++ )
	{
	    if ( self::isNonASCII($s[$i]) )
	    {
		$nbNonASCII++;
		if ( $stopOnFirstNonASCII )
		{
		    break;
		}
	    }
	    else
	    {
		$nbASCII++;
	    }
	}

	if ( $nbNonASCII <= 0 )
	{
	    return self::ASCII;
	}

	return ($nbNonASCII > $nbASCII ? self::NO_ASCII : self::ASCII_NO_ASCII );
    }

    /**
     * Determines whether the specified char is an no ascii char.
     */
    public static function isNonASCII($c)
    {
	return ( (($c >= "|") || ($c < " ")) && ($c != "\r") && ($c != "\n") && ($c != "\t") );
    }

// -------------------------------------------------------------------------->

    public static function encodeText($s, $charset=null, $transfertEncoding=null)
    {
	return self::encodeWord($s, $charset, $transfertEncoding, false);	
    }

    public static function encodeWord($s, $charset=null, $transfertEncoding=null, $word=true)
    {
	// TODO  charset ?
	$ascii = self::checkASCII($s, false);
	if ( $ascii == self::ASCII )
	{
	    return $s;
	}

	if ( empty($charset) )
	{
	    $charset = self::$DefaultCharset;
	}

	$binaryEncoding = false;
	if ( empty($transfertEncoding) )
	{
	    if ( $ascii != self::NO_ASCII )
	    {
		$transfertEncoding = self::Q_ENCODER_PREFIX;
	    }
	    else
	    {
		$transfertEncoding = self::B_ENCODER_PREFIX;
		$binaryEncoding = true;
	    }
	}
	else if ( strtolower($transfertEncoding) == strtolower(self::B_ENCODER_PREFIX) )
	{
	    $binaryEncoding = true;
	}
	else if ( strtolower($transfertEncoding) != strtolower(self::Q_ENCODER_PREFIX) )
	{
	    throw new MimeException("Unknown transfert encoding : " + $transfertEncoding);
	}

	return self::doEncode($s, $binaryEncoding, $charset, 68 - strlen($charset), "=?$charset?$transfertEncoding?", true, $word);
    }

    public static function doEncode($s, $binaryEncoding, $charset, $maxLength, $suffix, $noBreakLine, $word)
    {
	$length	= 0;
	if ( $binaryEncoding )
	{
	    $length = MimeBEncoder::encodedLength($s);
	}
	else
	{
	    $length = MimeQEncoder::encodedLength($s, $word);
	}

	//if length > max length ... multi pass
	$l = strlen($s);
	if ( ($length > $maxLength) && ($l > 1) )
	{
	    $sb = 
		self::doEncode(substr($s, 0, (int)($l / 2)), $binaryEncoding, $charset, $maxLength, $suffix, $noBreakLine, $word) .
		self::doEncode(substr($s, (int)($l / 2), $l), $binaryEncoding, $charset, $maxLength, $suffix, false, $word);

	    return $sb;
	}

	$encoder = null;
	$writer = new StringWriter();
	if ( $binaryEncoding )
	{
	    $encoder = new MimeBEncoder($writer);
	}
	else
	{
	    $encoder = new MimeQEncoder($writer, $word);
	}

	try
	{
	    $encoder->write($s);
	}
	catch(Exception $e)
	{}

	$sb = "";
	if ( !$noBreakLine )
	{
	    // nouvelle ligne
	    $sb = "\r\n ";
	}

	// charset et encoding type
	$sb = "$sb$suffix" . $writer->getBuffer();

	try
	{
	    $encoder->close();
	}
	catch(Exception $e)
	{}

	// encoding marker
	return "$sb?=";
    }

// -------------------------------------------------------------------------->

    public static function quote($s, $specials)
    {
        $l = strlen($s);
        $containsSpecial = false;

        for ( $i = 0 ; $i < $l ; $i++ )
        {
            $c = $s[$i];

            // must escape ?
            if ( ($c == '"') || ($c == '\\') || ($c == '\r') || ($c == '\n') )
            {
        	return self::enclose( self::escape($s) );
            }

            // contains special chars ?
            if( ($c < " ") || ($c >= "\177") || (strpos($specials, $c) !== false) )
            {
        	$containsSpecial = true;
            }
        }

        if ( $containsSpecial )
        {
            return self::enclose($s);
        }

        return $s;
    }

    private static function enclose($s)
    {
	return "\"$s\"";
    }

    private static function escape($s)
    {
	return addslashes($s);
    }
}
?>
