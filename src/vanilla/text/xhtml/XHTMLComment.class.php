<?php

import('vanilla.text.xhtml.XHTMLNode');

class XHTMLComment implements XHTMLNode
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
	return "#comment";
    }

    public function getNodeType()
    {
	return XHTMLNode::COMMENT_TYPE;
    }
    
//  ------------------------------------->

    public function getValue()
    {
	return $this->value;
    }

//  ------------------------------------->

    public function __toString()
    {
	return "<!--$this->value-->";
    }
}
?>
