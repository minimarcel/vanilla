<?php

import('vanilla.text.xhtml.XHTMLTag');
import('vanilla.text.xhtml.XHTMLText');
import('vanilla.text.xhtml.XHTMLComment');

class XHTMLParser
{
    private $xhtml;
    private $pos = 0;
    private $node = null;
    private $skipComment=true;

//  ------------------------------------->

    public function __construct($xhtml, $skipComment=true)
    {
	$this->xhtml = $xhtml;
	$this->skipComment = ($skipComment === true);
    }

//  ------------------------------------->

    public function getXHTML()
    {
	return utf8_encode($this->xhtml);
    }

//  ------------------------------------->

    public function getCurrentNode()
    {
	return $this->node;
    }

    public function nextNode()
    {
	do
	{
	    $this->parseNextNode();
	}
	while( $this->skipComment && !empty($this->node) && $this->node->getNodeType() == XHTMLNode::COMMENT_TYPE );

	return $this->node;
    }

//  ------------------------------------->

    private function parseNextNode()
    {
	// TODO, si on est dans un noeud qui comporte des portions de texte
	// comme un textarea, style, ou script, (regarder si il y en a d'autres)
	// on recherche la fin du tag lui même, ainsi on évite des problèmes de tags ou de caractères
	// illégaux contenus dedans
	// regarder d'ailleurs le type de noeud pour ça, pour voir si il existe des traitements particuliers

	$this->node = null;
	if ( $this->pos >= mb_strlen($this->xhtml) )
	{
	    return;
	}

	$startTag = mb_strpos($this->xhtml, '<', $this->pos);

	// text ?
	if ( $startTag === false || $startTag > 0  )
	{
	    if ( $this->parseTextNode($startTag === false ? strlen($this->xhtml) : $startTag) )
	    {
		return;
	    }
	}
	
	// fin 
	if ( $startTag === false )
	{
	    return;
	}
	
	// pas de fin au tag ?
	if ( $startTag == mb_strlen($this->xhtml)-1 )
	{
	    throw new Exception("Invalid char[<] at pos [$startTag]");
	}

	// comment ou autre ?
	if ( mb_substr($this->xhtml, $startTag+1, 1) == '!' ) 
	{
	    $this->parseCommentNode($startTag);
	}
	// tag classique
	else
	{
	    $this->parseTagNode($startTag);
	}
    }

    private function parseTextNode($to)
    {
	$s = mb_substr($this->xhtml, $this->pos, $to - $this->pos);
	$this->pos = $to;

	// on vérifie que le texte n'est pas complètement vide
	$s = str_replace("\r\n", ' ', $s);
	$s = str_replace("\n", ' ', $s);
	$s = str_replace("  ", ' ', $s); // TODO remplacer les espaces en trop !

	// $t = trim($s);
	// Laisse les chaines vides FIXME y a t'il des cas ou on ne veut pas de chaine vide ???
	// ou garder qu'un seul espace ????
	$t = $s; 
	if ( empty($t) )
	{
	    return false;
	}

	$this->node = new XHTMLText( $this->decodeString($s) );

	return true;
    }

    private function parseTagNode($start)
    {
	$end = mb_strpos($this->xhtml, '>', $start);	
	if ( $end === false )
	{
	    throw new Exception("Invalid end of tag at pos [$start]");
	}

	$s = trim(mb_substr($this->xhtml, $start+1, $end-$start-1));
	$this->pos = $end+1;

	// empty or closed ?
	$closed	= mb_substr($s, 0, 1) === '/';
	$empty	= mb_substr($s, -1) === '/';

	if ( $empty )
	{
	    $s = mb_substr($s, 0, -1);
	}
	else if ( $closed )
	{
	    $s = mb_substr($s, 1);
	}

	$tagName = '';
	$attributes = new StringMap();

	// on parse le node
	$inValue = false;
	$inQuote= false;
	$attName = null;
	$attValue = null;

	for ( $i = 0 ; $i < mb_strlen($s) ; $i++ )
	{
	    $c = mb_substr($s, $i, 1);

	    if ( $c == ' ' && !$inQuote )
	    {
		if ( $attName != null )
		{
		    // espace de séparation
		    if ( empty($tagName) )
		    {
			$tagName = $attName;
		    }
		    else
		    {
			$attributes->put($attName, $attValue = null ? $attName : $attValue);
		    }
		}

		$attName = null;
		$attValue = null;
	    }
	    else if ( $inValue )
	    {
		if ( $inQuote )
		{
		    if ( $c == '"' )
		    {
			// fin des quotes
			$inQuote = false;
			$inValue = false;
		    }
		    else
		    {
			$attValue .= $c;
		    }
		}
		else if ( $c == '"' )
		{
		    $inQuote = true; 
		}
		else
		{
		    $attValue .= $c;
		}
	    }
	    else if ( $c == '=' )
	    {
		$inValue = true;
		$attValue = '';
	    }
	    else
	    {
		$attName .= $c;
	    }
	}

	if ( $attName != null )
	{
	    // on gère le dernier element
	    if ( empty($tagName) )
	    {
		$tagName = $attName;
	    }
	    else
	    {
		$attributes->put($attName, $attValue == null ? $attName : $attValue);
	    }
	}

	$this->node = new XHTMLTag($tagName, $closed, $empty, $closed ? null : $attributes);
    }

    private function parseCommentNode($start)
    {
	$end	    = false;
	$comment    = false;

	if ( mb_substr($this->xhtml, $start, 4) == '<!--' )
	{
	    $end	 = mb_strpos($this->xhtml, '-->', $start);
	    $start	+= 4;
	    $comment	 = true;
	}
	else
	{
	    $end	 = mb_strpos($this->xhtml, '>', $start);
	    $start	+= 2;
	    $comment	 = false;
	}

	if ( $end === false )
	{
	    throw new Exception("Invalid end of comment at pos [$start]");
	}

	$this->pos = $end + ($comment ? 3 : 1);

	$value = mb_substr($this->xhtml, $start, $end - $start);
	$this->node = new XHTMLComment( $this->decodeString($value) );
    }

    private function decodeString($s)
    {
	$s = html_entity_decode($s, ENT_COMPAT, 'UTF-8'); 
	return $s;
    }
}
?>
