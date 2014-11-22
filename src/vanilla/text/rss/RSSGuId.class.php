<?php

import('vanilla.text.rss.RSSXMLHelper');

/**
 *  
 */
class RSSGuId 
{
    private $id;
    private $permaLink=false;

//  ------------------------------------->

    public function __construct($id)
    {
	$this->id = $id;
    }

//  ------------------------------------->

    public function getId()
    {
	return $this->id;
    }

    public function isPermaLink()
    {
	return $this->permaLink;
    }

    public function setPermaLink($permaLink)
    {
	$this->permaLink = ($permaLink == true);
    }
    
//  ------------------------------------->

    public function serialize()
    {
	return "" .
	    RSSXMLHelper::serializeNodeWithAttributes("guid", $this->id, Array('isPermaLink' => $this->permaLink));
    }
}
?>
