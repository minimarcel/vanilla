<?php

/**
 *  
 */
class RSSNamespace
{
    private $prefix;
    private $url;

//  ------------------------------------->

    public function __construct($prefix, $url)
    {
	$this->prefix	= $prefix;	
	$this->url	= $url;
    }

//  ------------------------------------->

    public function getPrefix()
    {
	return $this->prefix;
    }

    public function getURL()
    {
	return $this->url;
    }

//  ------------------------------------->

    public function serialize()
    {
	return "xmlns:$this->prefix=\"$this->url\"";
    }
}
?>
