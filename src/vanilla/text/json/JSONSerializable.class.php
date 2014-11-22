<?php

if ( class_exists('JsonSerializable') )
{
	die("Since PHP 5.4 JSONSerializable is used by the system, use VJSONSerializable instead");
	return;
}

import('vanilla.text.json.JSONPropertyBag');
import('vanilla.text.json.JSONPropertyVal');
//import('vanilla.text.json.VJSONSerializable');

interface JSONSerializable
{
    /**
     * Retourne un JSONPropertyValue 
     * contenant l'objet sérialisé
     */
    // public function toJSON(); 
}
?>
