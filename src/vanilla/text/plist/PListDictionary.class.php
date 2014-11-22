<?php

import('vanilla.util.StringMap');
import('vanilla.text.plist.PListProperty');
import('vanilla.text.plist.PListHelper');

class PListDictionary implements PListProperty
{
    private $values;

//  -------------------------------------------------->

    public function __construct()
    {
	$this->values = new StringMap();
    }

//  -------------------------------------------------->

    /**
     * Récupère toutes les valeurs
     */
    public function getValues()
    {
	return $this->values;
    }

    public function put($key, $value)
    {
	$this->values->put($key, PListHelper::toProperty($value));
	return $this;
    }

    /** 
     * Récupère une valeur depuis une key
     */
    public function get($key)
    {
	// TODO gestion des paths, est-ce un path, alors on chope la valeur pour un path, sinon on récupère la valeur cache
	// on alors même toutes les clés sont des paths
	return $this->values->get($key);
    }

//  -------------------------------------------------->

    public function getType()
    {
	return "dictionary";
    }

    public function serialize(DOMDocument $dom)
    {
	$root = $dom->createElement("dict");
	foreach ( $this->values->keys() as $k )
	{
	    $root->appendChild($dom->createElement("key", $k));
	    $root->appendChild($this->values->get($k)->serialize($dom));
	}

	return $root;
    }

//  -------------------------------------------------->

    ///**
    // * Récupère depuis un path
    // */
    //public function get($path)
    //{
    //    $p = explode('/', $path);
    //    if ( empty($p) )
    //    {
    //        return null;
    //    }

    //    return $this->getFromPath($p);
    //}

    //private function getFromPath($p)
    //{
    //    $k = explode(':', array_shift($p));	
    //    $val = $this->getByKey($k[0]);

    //    if ( $val instanceof ArrayList )
    //    {
    //        if ( sizeof($k) == 2 )
    //        {
    //    	$val = $val->get($k[1]);
    //        }
    //        else if ( !empty($p) )
    //        {
    //    	$val = $val->get(0);
    //        }
    //    }

    //    if ( empty($p) || !($val instanceof ITunesPropertyDictionnary) )
    //    {
    //        return $val;
    //    }

    //    return $val->getFromPath($p);
    //}
}
