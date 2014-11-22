<?php

import('vanilla.text.rss.RSSDocument');
import('vanilla.text.rss.RSSItem');
import('vanilla.text.rss.RSSChannelQualifiedNodes');
import('vanilla.text.rss.RSSSyndication');
import('vanilla.text.rss.RSSAtomLink');
import('vanilla.util.ArrayList');

/**
 *  
 */
class RSSChannel
{
    private $document;

    // mandatory
    private $items;
    private $title;
    private $link;
    private $description;

    // optionnal
    private $language;
    private $copyright;
    private $managingEditor;
    private $webMaster;
    private $pubDate;
    private $lastBuildDate;
    private $categories;
    private $generator = "Wisy RSS Generator";
    //private $docs;
    //private $cloud;
    private $ttl; // in minutes
    private $image;
    //private $textInput;
    //private $skipHours; // sous élément avec hour
    //private $skipDays; // sous élement avec day

    private $qualifiedNodes;

//  ------------------------------------->

    public function __construct($title, $link, $description)
    {
	$this->items = new ArrayList();
	$this->title = $title;
	$this->link = $link;
	$this->description = $description;
    }

//  ------------------------------------->

    public function setDocument(RSSDocument $document)
    {
	$this->document = $document;

	if ( !empty($this->qualifiedNodes) && !$this->qualifiedNodes->isEmpty() )
	{
	    foreach ( $this->qualifiedNodes->elements as $nodes )
	    {
		$this->document->addNamespace($nodes->getNamespace());
	    }
	}
    }

    public function getDocument()
    {
	return $this->document;
    }

    public function getTitle()
    {
	return $this->title;
    }

    public function getLink()
    {
	return $this->link;
    }

    public function getDescription()
    {
	return $this->description;
    }

    public function setLanguage(Language $language)
    {
	$this->language = $language;
    }

    public function getLanguage()
    {
	return $this->language;
    }

    public function setCopyright(/*string*/ $copyright)
    {
	$this->copyright = $copyright;
    }

    public function getCopyright()
    {
	return $this->copyright;
    }

    public function setManagingEditor(InternetAddress $managingEditor)
    {
	$this->managingEditor = $managingEditor;
    }

    public function getManagingEditor()
    {
	return $this->managingEditor;
    }

    public function setWebMaster(InternetAddress $webMaster)
    {
	$this->webMaster = $webMaster;
    }

    public function getWebMaster()
    {
	return $this->webMaster;
    }

    public function setPubDate(Date $pubDate)
    {
	$this->pubDate = $pubDate;
    }

    public function getPubDate()
    {
	return $this->pubDate;
    }

    public function setLastBuildDate(Date $lastBuilDate)
    {
	$this->lastBuilDate = $lastBuilDate;
    }

    public function getLastBuildDate()
    {
	return $this->lastBuilDate;
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

    public function setGenerator(/*string*/ $generator)
    {
	$this->generator = $generator;
    }

    public function getGenerator()
    {
	return $this->generator;
    }

    public function setTimeToLive(/*int*/ $ttl)
    {
	$this->ttl = $ttl;
    }

    public function getTimeToLive()
    {
	return $this->ttl;
    }

    public function setImage(/*string*/ $image)
    {
	$this->image = $image;
    }

    public function getImage()
    {
	return $this->image;
    }

    public function addItem(RSSItem $item)
    {
	$this->items->add($item);
	$item->setChannel($this);
    }

    public function getItems()
    {
	return $this->items;
    }

    public function setSyndication(RSSSyndication $syndication)
    {
	$this->addQualifiedNodes($syndication);
    }

    public function addAtomLink(RSSAtomLink $link)
    {
	$this->addQualifiedNodes($link);
    }
    
//  ------------------------------------->

    /**
     * Add a qualified node
     */
    public function addQualifiedNodes(RSSChannelQualifiedNodes $nodes)
    {
	if ( empty($this->qualifiedNodes) )
	{
	    $this->qualifiedNodes = new ArrayList();
	}

	$this->qualifiedNodes->add($nodes);	
	
	if ( !empty($this->document) )
	{
	    $this->document->addNamespace($nodes->getNamespace());
	}
    }

//  ------------------------------------->

    // TODO pour la sérialisation utiliser quelque chose de plus générique pour ecrire du xml
    public function serialize()
    {
	$s =  
	"<channel>\n";
	    
	$s .= RSSXMLHelper::serializeNode("title",		$this->title,	    false, true);
	$s .= RSSXMLHelper::serializeNode("link",	    	$this->link,	    false, true);
	$s .= RSSXMLHelper::serializeNode("description",   	$this->description, true, true);

	$s .= RSSXMLHelper::serializeNode("language",	    	$this->language);
	$s .= RSSXMLHelper::serializeNode("copyright",	    	$this->copyright);
	$s .= RSSXMLHelper::serializeNode("managingEditor",	$this->managingEditor);
	$s .= RSSXMLHelper::serializeNode("webMaster",		$this->webMaster);
	$s .= RSSXMLHelper::serializeNode("pubDate",	    	$this->pubDate);
	$s .= RSSXMLHelper::serializeNode("lastBuildDate", 	$this->lastBuildDate);
	$s .= RSSXMLHelper::serializeNode("generator",	    	$this->generator);
	$s .= RSSXMLHelper::serializeNode("ttl",	    	$this->ttl);
	$s .= RSSXMLHelper::serializeNode("image",	    	$this->image);

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

	if ( !$this->items->isEmpty() )
	{
	    foreach ( $this->items->elements as $item )
	    {
		$s .= "\t" . $item->serialize() . "\n";
	    }
	}

	$s .= "\n</channel>";

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
