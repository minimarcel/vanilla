<?php

import('vanilla.text.rss.RSSChannel');
import('vanilla.text.rss.RSSItemQualifiedNodes');
import('vanilla.text.rss.RSSContentEncoded');
import('vanilla.text.rss.RSSGuId');

/**
 *  
 */
class RSSItem
{
    private $channel;

    private $title;
    private $link;
    private $description;
    private $author;
    private $categories;
    //private $comments;
    //private $enclosure;
    private $guid;
    private $pubDate;
    //private $source;

    private $qualifiedNodes;

//  ------------------------------------->

    public function __construct()
    {}

//  ------------------------------------->

    // le channel doit avoir un document ... voir comment régler ça ..
    public function setChannel(RSSChannel $channel)
    {
	$this->channel = $channel;
	if ( !empty($this->qualifiedNodes) && !$this->qualifiedNodes->isEmpty() )
	{
	    foreach ( $this->qualifiedNodes->elements as $nodes )
	    {
		$this->channel->getDocument()->addNamespace($nodes->getNamespace());
	    }
	}
    }

    public function getChannel()
    {
	return $this->channel;
    }

    public function getTitle()
    {
	return $this->title;
    }

    public function setTitle($title)
    {
	$this->title = $title;
    }

    public function getLink()
    {
	return $this->link;
    }

    public function setLink($link)
    {
	$this->link = $link;
    }

    public function getDescription()
    {
	return $this->description;
    }

    public function setDescription($description)
    {
	$this->description = $description;
    }

    public function setAuthor(InternetAddress $author)
    {
	$this->author = $author;
    }

    public function getAuthor()
    {
	return $this->author;
    }

    public function setPubDate(Date $pubDate)
    {
	$this->pubDate = $pubDate;
    }

    public function getPubDate()
    {
	return $this->pubDate;
    }

    public function addCategory($category)
    {
	if ( empty($this->categories) )
	{
	    $this->categories = new ArrayList();
	}

	$this->categories->add($category);
    }

    public function getCategories()
    {
	return $this->categories;
    }

    public function setGuid(RSSGuId $guid)
    {
	$this->guid = $guid;
    }

    public function getGuid()
    {
	return $this->guid;
    }

    public function setContentEncoded(RSSContentEncoded $contentEncoded)
    {
	$this->addQualifiedNodes($contentEncoded);
    }
    
//  ------------------------------------->

    /**
     * Add a qualified node; required to be added to a rss channel first
     */
    public function addQualifiedNodes(RSSItemQualifiedNodes $nodes)
    {
	if ( empty($this->qualifiedNodes) )
	{
	    $this->qualifiedNodes = new ArrayList();
	}

	$this->qualifiedNodes->add($nodes);	

	if ( !empty($this->channel) )
	{
	    $this->channel->getDocument()->addNamespace($nodes->getNamespace());
	}
    }
    
//  ------------------------------------->

    public function serialize()
    {
	$s =  
	"<item>\n";
	    
	$s .= RSSXMLHelper::serializeNode("title",		$this->title);
	$s .= RSSXMLHelper::serializeNode("link",	    	$this->link);
	$s .= RSSXMLHelper::serializeNode("description",   	$this->description, true);

	$s .= RSSXMLHelper::serializeNode("author",	    	$this->author);
	$s .= RSSXMLHelper::serializeNode("pubDate",	    	$this->pubDate);

	if ( !empty($this->guid) )
	{
	    $s .= $this->guid->serialize();
	}

	if ( !empty($this->categories) )
	{
	    foreach ( $this->categories->elements as $category )
	    {
		$s .= RSSXMLHelper::serializeNode("category", $category);
	    }
	}

	if ( !empty($this->qualifiedNodes) && !$this->qualifiedNodes->isEmpty() )
	{
	    foreach ( $this->qualifiedNodes->elements as $q )
	    {
		$s .= "\t" . $q->serialize() . "\n";
	    }
	}

	$s .= "\n</item>";

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
	    severe("While serializing RSSChannel object : $s", $e);
	    return null;
	}
    }
}
?>
