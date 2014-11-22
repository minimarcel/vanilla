<?php

import('vanilla.text.css.CSSProperty');

class CSSDeclaration
{
    private /*ArrayList*/$properties;

//  ------------------------------------->
    
    public function __construct()
    {
	$this->properties = new ArrayList();
    }

//  ------------------------------------->

    public function addProperty($property)
    {
	$this->properties->add($property);
    }

    public function getProperties()
    {
	return $this->properties;
    }

    public function merge(CSSDeclaration $decl, $override=true)
    {
	foreach ( $decl->properties->elements as $prop )
	{
	    // FIXME la méthode en commentaire modifie les propriétés, 
	    // les match suivants voient la propriété changée
	    // ensuite c'est plus compliquer, on peut pas remplacer un margin tout court par un autre, 
	    // surtout si ensuite il y a du margin-top & co
	    if ( $override )
	    {
		//$existing = $this->findPropertyByName($prop->getName());
		//if ( !empty($existing) )
		//{
		//    $existing->setValue($prop->getValue());    
		//    continue;
		//}
	    }

	    $this->addProperty($prop);
	}
    }

    public function findPropertyByName($name)
    {
	foreach ( $this->properties->elements as $prop )
	{
	    if ( $prop->getName() == $name )
	    {
		return $prop;
	    }
	}

	return null;
    }

//  ------------------------------------->

    public function __toString()
    {
	$s = "";
	foreach ( $this->properties->elements as $property )
	{
	    $s .= "$property ";
	}

	return $s;
    }
}
?>
