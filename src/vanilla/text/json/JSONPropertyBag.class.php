<?php

import('vanilla.text.json.JSONProperty');
import('vanilla.text.json.JSONPropertyValue');
import('vanilla.text.json.VJSONSerializable');
import('vanilla.util.ArrayList');

/**
 *  
 */
class JSONPropertyBag implements JSONPropertyValue
{
    private $properties;

//  ------------------------------------->

    public function __construct()
    {
	$this->properties = new ArrayList();
    }

//  ------------------------------------->

    public function getProperties()
    {
	return $this->properties;
    }

    // deprecated ???
    public function add($name, $value)
    {
	$property = new JSONProperty($name, $this->serializeValue($value));
	$this->properties->add($property);

	return $this;
    }

    public function put($name, $value)
    {
	$p = $this->findPropertyByName($name);
	if ( !empty($p) )
	{
	    // FIXME utiliser la récupération de l'index serait plus rapide
	    $this->properties->removeForValue($p);
	}

	$property = new JSONProperty($name, $this->serializeValue($value));
	$this->properties->add($property);

	return $this;
    }

    public function findPropertyByName($name)
    {
	foreach ( $this->properties->elements as $p )
	{
	    if ( $p->getName() == $name )
	    {
		return $p;
	    }
	}

	return null;
    }

    private function serializeValue($value)
    {
	if ( $value instanceof VJSONSerializable )
	{
	    $value = $value->toJSON();
	}
	else if ( $value instanceof ArrayList )
	{
	    $value = $value->elements;
	}

	if ( is_array($value) )
	{
	    for ( $i = 0 ; $i < sizeof($value) ; $i++ )
	    {
		$value[$i] = $this->serializeValue($value[$i]);
	    }
	}

	return $value;
    }

//  ------------------------------------->

    public function serialize()
    {
	$s = "";
	foreach ( $this->properties->elements as $property )
	{
	    $s .= empty($s) ? "" : ",";
	    $s .= $property->serialize();
	}

	return "{" . $s . "}";
    }

    public function __toString()
    {
	try
	{
	    return $this->serialize();
	}
	catch(Exception $e)
	{
	    $s = serialize($this);
	    severe("While serializing json object : $s", $e);
	    return '{"error":"error"}';
	}
    }

//  ------------------------------------->

    /**
     * A shortcut for inline coding, ex : JSONPropertyBag::Create()->add("name", "value");
     */
    public static function Create()
    {
	return new JSONPropertyBag();
    }
}
?>
