<?php

import('vanilla.text.rss.RSSXMLHelper');
import('vanilla.text.rss.RSSChannel');
import('vanilla.text.rss.RSSNamespace');
import('vanilla.util.StringMap');

/**
 *  
 */
class RSSDocument
{
    private $channel;
    private $version = "2.0";
    private $namespaces;

//  ------------------------------------->

    public function __construct()
    {
	$this->namespaces = new StringMap();
    }

//  ------------------------------------->

    public function addNamespace(RSSNamespace $namespace)
    {
	$this->namespaces->put($namespace->getPrefix(), $namespace);
    }

    public function setChannel(RSSChannel $channel)
    {
	$this->channel = $channel;
	$channel->setDocument($this);
    }

    public function getChannel()
    {
	return $this->channel;
    }

//  ------------------------------------->

    // TODO pour la sérialisation utiliser quelque chose de plus générique pour ecrire du xml
    public function serialize()
    {
	$s =  
	"<rss version=\"$this->version\"";

	foreach ( $this->namespaces->elements as $namespace )
	{
	    $s .= " " . $namespace->serialize();
	}

	$s .= ">\n";

	if ( !empty($this->channel) )
	{
	    $s .= $this->channel->serialize();
	}

	$s .= "\n</rss>";

	return $s;
    }

    public function __toString()
    {
	try
	{
	    return $this->serialize();
	}
	catch(Exception $e)
	{
	    $s = serialize($this);
	    severe("While serializing RSSDocument object : $s", $e);
	    return null;
	}
    }
}
?>
