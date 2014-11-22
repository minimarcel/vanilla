<?php

import('vanilla.text.json.JSONPropertyBag');
import('vanilla.text.DateFormat');
import('vanilla.util.Date');

/**
 *  
 */
class JSONSerializer
{
    const JSON_DATE_PATTERN = "%Y-%m-%dT%H:%M:%S.%zZ";

//  ------------------------------------->

    public static function serializeValue(/*mixed*/$v)
    {
	if ( !isset($v) )
	{
	    return "null";
	}
	else if ( is_array($v) )
	{
	    $s = "";
	    foreach ( $v as $t )
	    {
		$s .= strlen($s) <= 0 ? "" : ",";
		$s .= self::serializeValue($t);
	    }

	    return "[$s]";
	}
	else if ( is_bool($v) )
	{
	    return ($v ? "true" : "false");
	}
	else if ( is_int($v) || is_float($v) || is_double($v) || is_long($v) )
	{
	    return str_replace(",", ".", "$v");
	}
	else if ( $v instanceof Date )
	{
	    return "\"" . DateFormat::getForPattern(self::JSON_DATE_PATTERN)->format($v) . "\"";
	}
	else if ( $v instanceof JSONPropertyValue )
	{
	    return $v->serialize();
	}

	// FIXME jquery n'accepte pas les simple quote escapée dans du json !!!! 
	// alors on les escape pas
	return "\"" . strjs("$v", true) . "\"";
    }
}
?>
