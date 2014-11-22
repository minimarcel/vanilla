<?php

import('vanilla.text.plist.PListProperty');
import('vanilla.text.plist.PListHelper');

class PListPrimitive implements PListProperty
{
    const STRING_TYPE	= 'string';
    const INTEGER_TYPE	= 'integer';
    const REAL_TYPE	= 'real';
    const BOOLEAN_TYPE	= 'boolean';

//  -------------------------------------------------->

    private $value;
    private $type;

//  -------------------------------------------------->

    public function __construct($val)
    {
	if ( is_int($val) )
	{
	    $this->type = self::INTEGER_TYPE;
	}
	else if ( is_double($val) || is_float($val) )
	{
	    $this->type = self::REAL_TYPE;
	}
	else if ( is_bool($val) )
	{
	    $this->type = self::BOOLEAN_TYPE;
	}
	else if ( is_string($val) )
	{
	    $this->type = self::STRING_TYPE;
	}
	else
	{
	    // FIXME PListException
	    throw new Exception("Invalid value type : $val");
	}

	$this->value = $val;
    }

//  -------------------------------------------------->

    /**
     * Récupération de la valeur
     */
    public function getValue()
    {
	return $this->value;
    }

    /**
     * Récupération de la valeur
     */
    public function val()
    {
	return $this->value;
    }

    public function getPrimitiveType()
    {
	return $this->type;
    }

//  -------------------------------------------------->

    public function getType()
    {
	return "primitive:" . $this->type;
    }

    public function serialize(DOMDocument $dom)
    {
	if ( $this->type == self::BOOLEAN_TYPE )
	{
	    return $dom->createElement($this->value ? 'true' : 'false');
	}

	// FIXME how are serialized real ?
	$t = $dom->createTextNode(strval($this->value));
	$e = $dom->createElement($this->type);
	$e->appendChild($t);

	return $e;
    }
}
