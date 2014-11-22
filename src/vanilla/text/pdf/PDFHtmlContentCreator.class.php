<?php

import("vanilla.io.File");
import("vanilla.io.FileReader");
import("vanilla.net.WebURL");
import("vanilla.text.css.CSSParser");
import("vanilla.text.css.CSSStyleSelector");
import("vanilla.text.xhtml.XHTMLTag");

// TODO mutualiser avec le MailHtmlCreator
class PDFHtmlContentCreator
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
	   TODO On récupère les feuilles de style
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
		    if ( !empty($link) && substr($link, 0, 7) != 'http://' )
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
			$src = File::fromRelativeURL($src)->__toString();
			$item->setAttribute("src", $src);
		    }
		}
	    }

            $this->prepareChildNodes($item); 
        }
    }

// -------------------------------------------------------------------------->

    public function writeTo(HTML2PDF $pdf)
    {
	$pdf->writeHTML($this->getHtml());
	mb_internal_encoding("UTF-8");
    }

    public function getHtml()
    {
	$result = $this->dom->saveHTML();

	/*
	    On cut le body
	    FIXME doit-on tout inclure ?
	*/

	$result = substr($result, strpos($result, "<body>") + strlen("<body>"));
	$result = substr($result, 0, strrpos($result, "</body>"));

	return $result;
    }

    /*
     * Renvoie le DOM
     */
    public function getDom()
    {
	return $this->dom;
    }
}
?>
