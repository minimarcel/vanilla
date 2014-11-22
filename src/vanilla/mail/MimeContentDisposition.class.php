<?php

class MimeContentDisposition
{
    const ATTACHMENT	= "attachment";
    const INLINE	= "inline";

// -------------------------------------------------------------------------->

    private $disposition;
    private $parameters;
    
// -------------------------------------------------------------------------->
    
    public function __construct($disposition, $parameters=null)
    {
	if ( empty($disposition) ) 
	{
	    throw new MimeException('disposition must be declared');
	}

	$this->disposition  = $disposition;
	$this->parameters   = $parameters;
    }

// -------------------------------------------------------------------------->

    public function getDisposition()
    {
	return $this->disposition;
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

    public function getLine()
    {
	$s = $this->disposition;

	if ( !empty($this->parameters) )
	{
	    $s = $s . $this->parameters->getLine(strlen($s) + 21); 
	}

	return $s;
    }

    public function __toString()
    {
	return $this->getLine();
    }
}
?>
