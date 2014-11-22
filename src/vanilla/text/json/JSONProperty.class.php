<?php

import('vanilla.text.json.JSONPropertyBag');
import('vanilla.text.json.JSONSerializer');
import('vanilla.text.DateFormat');
import('vanilla.util.Date');

/**
 *  
 */
class JSONProperty
{
    private /*string*/$name;
    private /*mixed*/ $value;

//  ------------------------------------->

    public function __construct($name, $value)
    {
	$this->name	= $name;
	$this->value	= $value;
    }

//  ------------------------------------->

    public function getName()
    {
	return $this->name;
    }

    public function getValue()
    {
	return $this->value;
    }

//  ------------------------------------->

    public function serialize()
    {
	return "\"$this->name\":" . $this->serializeValue($this->value);
    }

    private function serializeValue($v)
    {
	return JSONSerializer::serializeValue($v);
    }
}
?>
