<?php

import('vanilla.text.rss.RSSChannelQualifiedNodes');
import('vanilla.text.rss.RSSNamespace');
import('vanilla.text.rss.RSSXMLHelper');

/**
 *  
 */
class RSSAtomLink implements RSSChannelQualifiedNodes
{
    const PREFIX    = 'atom';
    const CODE	    = 'atom:link';

//  ------------------------------------->

    const REL_ALTERNATE	    = 'alternate';
    const REL_RELATED	    = 'related';
    const REL_SELF	    = 'self';
    const REL_ENCLOSURE	    = 'enclosure';
    const REL_VIA	    = 'via';

//  ------------------------------------->

    private $namespace;

    private $href;
    private $rel;
    private $type;
    private $hrefLang;
    private $title;
    private $length;

//  ------------------------------------->

    public function __construct($href, $rel, $type)
    {
	$this->namespace = new RSSNamespace(self::PREFIX, "http://www.w3.org/2005/Atom");

	$this->href	= $href;
	$this->rel	= $rel;
	$this->type	= $type;
    }

//  ------------------------------------->

    public function getCode()
    {
	return self::CODE;
    }

    public function getNamespace()
    {
	return $this->namespace;
    }

    public function getHref()
    {
	return $this->href;
    }

    public function getRel()
    {
	return $this->rel;
    }

    public function getType()
    {
	return $this->type;
    }

    public function setHrefLang(Language $language)
    {
	$this->hrefLang = $language;
    }

    public function getHrefLang()
    {
	return $this->hrefLang;
    }

    public function getTitle()
    {
	return $this->title;
    }

    public function setTitle(/*string*/ $title)
    {
	$this->title = $title;
    }

    public function getLength()
    {
	return $this->length;
    }

    public function setLength(/*string*/ $length)
    {
	$this->length = $length;
    }
    
//  ------------------------------------->

    public function serialize()
    {
	$prefix = $this->namespace->getPrefix();

	return RSSXMLHelper::serializeNodeWithAttributes
	(
	    "$prefix:link", null, 
	    Array
	    (
		"href"		=> $this->href, 	
		"rel"		=> $this->rel, 	
		"type"		=> $this->type, 	
		"hrefLang"	=> $this->hrefLang, 	
		"title"		=> $this->title, 	
		"length"	=> $this->length
	    ),
	    false, true
	);
    }
}
?>
