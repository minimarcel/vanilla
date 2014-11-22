<?php

import('vanilla.util.ArrayList');
import('vanilla.text.plist.PListProperty');
import('vanilla.text.plist.PListHelper');

class PListArray implements PListProperty
{
    private $values;

//  -------------------------------------------------->

    public function __construct()
    {
	$this->values = new ArrayList();
    }

//  -------------------------------------------------->

    /**
     * Récupère les valeurs
     */
    public function getValues()
    {
	return $this->values;
    }

    /**
     * Ajoute une valeur
     */
    public function add($value)
    {
	$this->values->add(PListHelper::toProperty($value));
	return $this;
    }

    /**
     * Ajoute une liste de valeurs
     */
    public function addAll($value)
    {
	if ( $value instanceof ArrayList )
	{
	    $value = $value->elements;
	}

	if ( !is_array($value) )
	{
	    return $this->add($value);
	}

	foreach ( $value as $v )
	{
	    $this->add($value);
	}

	return $this;
    }

    /** 
     * Récupère depuis un index
     */
    public function get($index)
    {
	return $this->values->get($index);
    }

//  -------------------------------------------------->

    public function getType()
    {
	return "array";
    }

    public function serialize(DOMDocument $dom)
    {
	$root = $dom->createElement("array");
	foreach ( $this->values->elements as $e )
	{
	    $root->appendChild($e->serialize($dom));
	}

	return $root;
    }
}
