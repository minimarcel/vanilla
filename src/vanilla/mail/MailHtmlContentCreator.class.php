<?php

import("vanilla.io.File");
import("vanilla.io.FileReader");
import("vanilla.net.WebURL");
import("vanilla.text.css.CSSParser");
import("vanilla.text.css.CSSStyleSelector");
import("vanilla.text.xhtml.XHTMLTag");

class MailHtmlContentCreator
{
    private $content;
    private $dom;
    private $selector;

// -------------------------------------------------------------------------->

    public function __construct($content, $charset="utf-8")
    {
	$this->content = $content;
	$this->dom = new DOMDocument("1.0", $charset);
	$this->dom->loadHTML($content);
	$this->selector = new CSSStyleSelector();

	/*
	   On récupère les feuilles de style
	   TODO
	*/

	/*
	   On récupère les tags style
	*/

	$list = $this->dom->getElementsByTagName("style");
	for ( $i = 0 ; $i < $list->length ; $i++ )
	{
	    $item = $list->item($i);
	    $p = new CSSParser($item->nodeValue);
	    while ( $p->hasNextRule() )
	    {
		$rule = $p->getRule();
		$this->selector->addRule($rule);
	    }

	    // on supprime le noeud
	    $item->parentNode->removeChild($item);
	}

	/*
	    On traverse le doc 
	    pour en déduire les noeuds pour chaque style
	    et pour vérifier que les urls sont bien en absolue
	*/

	$body = $this->dom->getElementsByTagName("body")->item(0);
	$this->prepareChildNodes($body);
    }

// -------------------------------------------------------------------------->

    private function prepareChildNodes($node)
    {
        $list = $node->childNodes;
	if ( empty($list) )
	{
	    return;
	}

        for ( $i = 0 ; $i < $list->length ; $i++ )
        {
            $item = $list->item($i);
	    if ( $item instanceof DOMElement )
	    {
		// TODO vérifer que les urls sont bien en absolue pour le style ?
		$declaration = $this->selector->styleForElement($item);
		$style = $item->getAttribute("style");
		$style = "$declaration$style";

		if ( empty($style) )
		{
		    $item->removeAttribute("style");
		}
		else
		{
		    $item->setAttribute("style", $style);
		}

		/*
		   On vérifie les valeurs absolues des images
		   et des liens
		*/

		$tagName = strtolower($item->tagName);
		if ( $tagName == "a" )
		{
			$link = $item->getAttribute("href");
		    if ( !empty($link) && !startsWith($link, "mailto:") && !startsWith($link, 'http://') && preg_match('/^\\[\\[.+\\]\\]$/', $link) == 0 )
		    {
			$link = WebURL::Create($link)->toAbsoluteString();
			$item->setAttribute("href", $link);
		    }
		}
		else if ( $tagName == "img" )
		{
		    $src = $item->getAttribute("src");
		    if ( !empty($src) && substr($src, 0, 7) != 'http://' )
		    {
			$src = WebURL::Create($src)->toAbsoluteString();
			$item->setAttribute("src", $src);
		    }
		}
	    }

            $this->prepareChildNodes($item); 
        }
    }

// -------------------------------------------------------------------------->

    public function getHtml()
    {
	$result = $this->dom->saveHTML();

	/*
	    On cut le body
	    FIXME doit-on tout inclure ?
	*/

	$result = substr($result, strpos($result, "<body>") + strlen("<body>"));
	$result = substr($result, 0, strrpos($result, "</body>"));
	
	/*
		On réextrait également les [[...]], ils ont été remplacé par des %5B%5B...%5D%5D
	*/
	
	$result = preg_replace("/%5B%5B(.+)%5D%5D/", "[[$1]]", $result);

	return $result;
    }

    /**
     * TODO gérer la transformation avec une classe passée en paramètres
     */
    public function getText()
    {
	$serializer = new MailHtmlContentCreator_TextSerializer();
	return $serializer->serializeDom($this->dom);
    }

    /*
     * Renvoie le DOM
     */
    public function getDom()
    {
	return $this->dom;
    }
}

// -------------------------------------------------------------------------->

// private class
class MailHtmlContentCreator_TextSerializer
{
    private $result = "";
    private $breaked = 1;

// -------------------------------------------------------------------------->
    
    public function serializeDom($dom)
    {
	$this->result = "";
	$this->breaked = 1;

	$body = $dom->getElementsByTagName("body")->item(0);
	$this->serializeNode($body);

	return $this->result;
    }

    private function serializeNode($node)
    {
        $list = $node->childNodes;
	if ( empty($list) )
	{
	    return;
	}

        for ( $i = 0 ; $i < $list->length ; $i++ )
        {
            $item = $list->item($i);

	    if ( $item instanceof DOMText )
	    {
		$t = trim($item->wholeText);
		if ( !empty($t) )
		{
		    // TODO gérer les espaces
		    if ( $this->breaked == 0 )
		    {
			$this->result .= " ";
		    }

		    $this->result	 .= trim($item->wholeText);
		    $this->breaked	 = 0;
		}
	    }
	    else if ( $item instanceof DOMElement )
	    {
		$name = strtolower($item->tagName);
		if ( XHTMLTag::isBreakingFlowTagName($name) )
		{
		    if ( $name == "br" )
		    {
			$this->result .= "\n";
			$this->breaked++;
		    }
		    else if ( XHTMLTag::isBlockTagName($name) )
		    {
			$this->breakLine(2);	
			$this->serializeNode($item); 
			$this->breakLine(2);	
		    }
		    else 
		    {
			$this->breakLine(1);
			$this->serializeNode($item); 
			$this->breakLine(1);	
		    }
		}
		else if ( $name == "img" )
		{
		    $this->result  .= " " . $item->getAttribute("alt") . " "; 
		    $this->breaked  = 0;
		}
		else if ( $name == "a" )
		{
		    $this->serializeNode($item); 
		    $this->result  .= " [" . $item->getAttribute("href") . "]"; 
		    $this->breaked  = 0;
		}
		else
		{
		    $this->serializeNode($item); 
		}
	    }
	    else
	    {
		$this->serializeNode($item); 
	    }
        }
    }

    private function breakLine($objectif)
    {
	while ( $this->breaked < $objectif )
	{
	    $this->result .= "\n";
	    $this->breaked++;
	}
    }
}
?>
