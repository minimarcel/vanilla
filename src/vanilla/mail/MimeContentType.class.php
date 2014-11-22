<?php

import('vanilla.mail.MimeParameterList');

class MimeContentType
{
    private $primaryType;
    private $subType;
    private $parameters;

// -------------------------------------------------------------------------->
    
    public function __construct($primaryType, $subType, $parameters=null)
    {
	if ( empty($primaryType) || empty($subType) )
	{
	    throw new MimeException('primary and sub types must be both declared');
	}

	$this->primaryType  = $primaryType;
	$this->subType	    = $subType;
	$this->parameters   = $parameters;
    }

// -------------------------------------------------------------------------->

    public function getPrimaryType()
    {
	return $this->primaryType;
    }

    public function getSubType()
    {
	return $this->subType;
    }

    public function getBaseType()
    {
	return ($this->primaryType . '/' . $this->subType);
    }

// -------------------------------------------------------------------------->

    public function getParameter($name)
    {
	if ( empty($this->parameters) )
	{
	    return null;
	}

	return $this->parameters->get($name);
    }

    public function getParameters()
    {
	return $this->parameters;
    }

    public function setParameter($name, $value)
    {
	if ( empty($this->parameters) )
	{
	    $this->parameters = new MimeParameterList();
	}

	$this->parameters->set($name, $value);
    }

// -------------------------------------------------------------------------->

    public function match($primaryType, $subType)
    {
	$type = new MimeContentType($primaryType, $subType);
	return $this->matchType($type);
    }

    public function matchType(MimeContentType $type)
    {
	if( empty($this->primaryType) || empty($type->primaryType) )
	{
	    return false;
	}

	if ( strtolower($this->primaryType) !== strtolower($type->primaryType) )
	{
	    return false;
	}

	if ( $this->subType[0] == '*' || $type->subType[0] == '*' )
	{
	    return true;
	}

	return ( strtolower($this->subType) === strtolower($this->subType) );
    }

// -------------------------------------------------------------------------->

    public function getLine()
    {
	$s = $this->getBaseType();

	if ( !empty($this->parameters) )
	{
	    $s = $s . $this->parameters->getLine(strlen($s) + 14); 
	}

	return $s;
    }

    public function __toString()
    {
	return $this->getLine();
    }

// -------------------------------------------------------------------------->

    public static function createFrom($primaryType, $subType)
    {
	$ct = new MimeContentType($primaryType, $subType);
	return $ct;
    }
}
?>
