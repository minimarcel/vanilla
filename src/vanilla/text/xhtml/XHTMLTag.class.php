<?php

import('vanilla.util.StringMap');
import('vanilla.text.xhtml.XHTMLNode');
import('vanilla.util.ArrayList');

class XHTMLTag implements XHTMLNode
{
    private static $BLOCK_TAGS = Array
    (
	"blockquote", "dd", "dir", "div", "dl", "dt", "h1", "h2", "h3", "h4", "h5", "h6", "li", 
	"menu", "noframes", "ol", "p", "pre", "td", "table", "title", "tr", "ul", "fieldset"
    );

    private static $BREAK_FLOW_TAGS = Array
    (
	"blockquote", "br", "center", "dd", "dir", "div", "dl", "dt", "form", "h1", "h2", "h3", "h4", "h5", "h6",
	"hr", "isindex", "li", "menu", "noframes", "ol", "p", "pre", "tr", "td", "th", "title", "ul", "iframe", 
	"address", "fieldset", "table"
    );

//  ------------------------------------->

    private $name;
    private $closed=false;
    private $empty=false;
    private $attributes;

//  ------------------------------------->

    public function __construct($name, $closed, $empty, $attributes)
    {
	$this->name	    = strtolower($name);
	$this->empty	    = $empty == true;
	$this->closed	    = $this->empty || $closed == true;
	$this->attributes   = $attributes;
    }

//  ------------------------------------->

    public function getNodeName()
    {
	return $this->name;
    }

    public function getNodeType()
    {
	return XHTMLNode::TAG_TYPE;
    }
    
//  ------------------------------------->

    public function isClosed()
    {
	return $this->closed;
    }

    public function isEmpty()
    {
	return $this->empty;
    }

    public function getAttributeNames()
    {
	if ( empty($this->attributes) )
	{
	    return null;
	}

	return $this->attributes->keys();
    }

    public function getAttribute($name)
    {
	if ( empty($this->attributes) )
	{
	    return null;
	}

	return $this->attributes->get($name);
    }

    public function getClosedTag()
    {
	if ( $this->closed )
	{
	    return null;
	}

	return new XHTMLTag($this->name, true, false, null);
    }

//  ------------------------------------->

    /**
     * 
     */
    public function isInline()
    {
	return self::isInlineTagName($this->name);
    }

    /**
     * Définit si ce tag structure le html
     */
    public function isBlock()
    {
	return self::isBlockTagName($this->name);
    }

    public function isFlow()
    {
	return self::isFlowTagName($this->name);
    }

    /**
     * Définit si ce tag cause un saut à la ligne.
     */
    public function isBreakingFlow()
    {
	return self::isBreakingFlowTagName($this->name);
    }

//  ------------------------------------->

    /**
     * 
     */
    public static function isInlineTagName($name)
    {
	// TODO mettre une variable statique
	return ArrayList::FromValues
	(
	    "a", "object", "applet", "img", "map", "iframe", "br", "span", "bdo", 
	    "big", "small", "font", "basefont", "tt", "i", "b", "u", "u", "s", "strike",
	    "sub", "sup", "em", "strong", "dfn", "code", "q", "samp", "kbd", "var", 
	    "cite", "abbr", "acronym", "sub", "sup", "em", "strong", "dfn", "code", 
	    "q", "samp", "kbd", "var", "cite", "abbr", "acronym", "input", "select", 
	    "textarea", "label", "button"
	)
	->contains($name);
    }

    /**
     * Définit si ce tag structure le html
     */
    public static function isBlockTagName($name)
    {
	return in_array(strtolower($name), self::$BLOCK_TAGS);
    }

    public static function isFlowTagName($name)
    {
	return in_array(strtolower($name), Array("form", "noscript", "ins", "del", "script")) || self::isBlockTagName($name) || self::isInlineTagName($name); 
    }

    /**
     * Définit si ce tag cause un saut à la ligne.
     */
    public static function isBreakingFlowTagName($name)
    {
	return in_array(strtolower($name), self::$BREAK_FLOW_TAGS);
    }

//  ------------------------------------->

    public function __toString()
    {
	if ( $this->closed && !$this->empty )
	{
	    return "</$this->name>";
	}

	$s = "<$this->name";

	if ( !empty($this->attributes) )
	{
	    foreach($this->attributes->elements as $name => $value)
	    {
		$s .= " $name=\"" . strxml($value) . "\"";	
	    }
	}

	if ( $this->empty )
	{
	    $s .= " /";
	}

	$s .= ">";

	return $s;
    }
}
?>
