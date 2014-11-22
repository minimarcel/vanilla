<?php

import('vanilla.text.xhtml.XHTMLNode');

class XHTMLText implements XHTMLNode
{
    private $value;

//  ------------------------------------->

    public function __construct($value)
    {
	$this->value = $value;
    }

//  ------------------------------------->

    public function getNodeName()
    {
	return "#text";
    }

    public function getNodeType()
    {
	return XHTMLNode::TEXT_TYPE;
    }
    
//  ------------------------------------->

    public function getValue()
    {
	return $this->value;
    }

//  ------------------------------------->

    public function __toString()
    {
	return $this->value;
    }
}
?>
