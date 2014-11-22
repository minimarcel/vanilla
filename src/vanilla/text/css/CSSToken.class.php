<?php

class CSSToken
{
    const START_DECLARATION		= "{";
    const END_DECLARATION		= "}";
    const END_INSTRUCTION		= ";";
    const PROPERTY_SEPARATOR		= ":";
    const SELECTOR_SEPARATOR		= ",";
    const SELECTOR_ID			= "#";
    const SELECTOR_CLASS		= ".";
    const SELECTOR_PSEUDO		= ":";
    const SELECTOR_CHILD		= ">";
    const SELECTOR_DESCENDANT		= " ";
    const SELECTOR_DIRECT_ADJACENT	= "+";
    const SELECTOR_INDIRECT_ADJACENT	= "~";
    const SPACE				= " ";

//  ------------------------------------------------->

    public $value;
    public $special = false;

//  ------------------------------------------------->

    public function __construct($value, $special=false)
    {
	$this->value	= $value;
	$this->special	= ($special == true);
    }

    public function __toString()
    {
	return $this->value;
    }
}
?>
