<?php
import('vanilla.util.Date');
import('vanilla.mail.InternetAddress');
import('vanilla.localisation.Language');

/**
 *  
 */
class RSSXMLHelper
{
    public static function serializeNode($name, $value, $useCData=false, $drawIfEmpty=false)
    {
	return self::serializeNodeWithAttributes($name, $value, null, $useCData, $drawIfEmpty);
    }

    public static function serializeNodeWithAttributes($name, $value, $attributes, $useCData=false, $drawIfEmpty=false)
    {
	$v = self::toString($value);
	if ( !empty($v) || $drawIfEmpty)
	{
	    $s = "\t<$name";
	    if ( !empty($attributes) )
	    {
		foreach ( $attributes as $attName => $attVal )
		{
		    $av = self::toString($attVal);
		    if ( !empty($av) )
		    {
			$s .= " $attName=\"" . strxml($av) . "\"";
		    }
		}
	    }

	    if ( empty($v) )
	    {
		$s .= " />";
	    }
	    else
	    {
		$s .= ">";

		if ( $useCData )
		{
		    $s .= "<![CDATA[$v]]>";
		}
		else
		{
		    $s .= strxml($v);
		}

		$s .= "</$name>\n";
	    }

	    return $s;
	}

	return "";
    }

    public static function toString($value)
    {
	if ( $value instanceof Language )
	{
	    return $value->getCode();
	}
	else if ( $value instanceof Date )
	{
	    return $value->rfc822();
	}
	else if ( $value instanceof InternetAddress )
	{
	    $v = $value->getAddress();

	    $personal = $this->getPersonal();
	    if ( !empty($personal) )
	    {
		$v .= " ($personal)";
	    }

	    return $v;
	}
	else if ( is_bool($value) ) 
	{
	    return ($value == true ? "true" : "false");
	}

	return "$value";
    }
}
?>
