<?php

import('vanilla.text.json.JSONPropertyValue');
import('vanilla.text.json.JSONSerializer');

/**
 *  
 */
class JSONPropertyVal implements JSONPropertyValue
{
    private $value;

//  ------------------------------------->

    public function __construct($value)
    {
	$this->value = $value;
    }

//  ------------------------------------->

    public function val()
    {
	return $this->value;
    }

//  ------------------------------------->

    public function serialize()
    {
	return JSONSerializer::serializeValue($this->value);
    }

    public function __toString()
    {
	try
	{
	    return $this->serialize();
	}
	catch(Exception $e)
	{
	    $s = serialize($this->value);
	    severe("While serializing json object : $s", $e);
	    return 'error';
	}
    }
}
?>
