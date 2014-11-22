<?php

import('vanilla.text.rss.RSSItemQualifiedNodes');
import('vanilla.text.rss.RSSNamespace');
import('vanilla.text.rss.RSSXMLHelper');

/**
 *  
 */
class RSSContentEncoded implements RSSItemQualifiedNodes
{
    const PREFIX    = 'content';
    const CODE	    = 'content:encoded';

//  ------------------------------------->

    private $namespace;
    private $encoded;

//  ------------------------------------->

    public function __construct($encoded)
    {
	$this->namespace = new RSSNamespace(self::PREFIX, "http://purl.org/rss/1.0/modules/content/");
	$this->encoded = $encoded;
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

    public function getEncoded()
    {
	return $this->encoded;
    }
    
//  ------------------------------------->

    public function serialize()
    {
	$prefix = $this->namespace->getPrefix();

	return "" .
	    RSSXMLHelper::serializeNode("$prefix:encoded", $this->encoded, true);
    }
}
?>
