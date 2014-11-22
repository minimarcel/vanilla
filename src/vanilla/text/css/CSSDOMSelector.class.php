<?php

import('vanilla.util.StringMap');
import('vanilla.util.Collections');
import('vanilla.text.css.CSSParser');
import('vanilla.text.css.CSSHelper');

class CSSDOMSelector
{
    private /*DOMDocument*/ $dom;
    private /*CSSSelector*/ $selector;
    private /*DOMElement*/ $context;

    private /*CSSSelector*/ $idSelector;
    private /*int*/ $idLevel = -1;
    private /*CSSSelector*/ $tagSelector;
    private /*int*/ $tagLevel = -1;


//  ------------------------------------->

    public function __construct(DOMDocument $dom, CSSSelector $selector, /*DOMElement*/ $context=null)
    {
	$this->dom	= $dom;
	$this->selector = $selector;
	$this->context	= empty($context) ? $this->dom->documentElement : $context;

	$this->init();
    }

//  ------------------------------------->

    private function init()
    {
	// on boucle sur les selectors
	for ( $level = 0, $sel = $this->selector ; !empty($sel) ; $sel = $sel->getTagHistory(), $level++ )
	{
	    $subId  = $sel->getSubSelectorTypeOfId();
	    $tag    = $sel->getTag();

	    if ( !empty($subId) )
	    {
		$this->idSelector = $sel;
		$this->idLevel = $level;

		// on n'a pas besoin de savoir ce qu'il y a au dessus
		break;
	    }

	    if ( empty($this->tagSelector) && !empty($tag) )
	    {
		$this->tagSelector = $sel;
		$this->tagLevel = $level;
	    }
	}
    }

//  ------------------------------------->

   public function find()
   {
	$a = new ArrayList();
	$p = null;

	if ( !empty($this->idSelector) )
	{
	    $id = $this->idSelector->getSubSelectorTypeOfId()->getValue();
	    $p	= $this->dom->getElementById($id);

	    if ( empty($p) || !$this->isInContext($p) || !CSSHelper::checkSelector($p, $this->idSelector) )
	    {
		return $a;
	    }

	    if ( $this->idLevel == 0 )
	    {
		$a->add($p);
		return $a;
	    }
	}

	if ( empty($p) )
	{
	    $p = $this->context;
	}

	if ( !empty($this->tagSelector) )
	{
	    if ( $this->tagLevel == 0 )
	    {
		$this->findAll($a, $p, $this->tagSelector->getTag());
	    }
	    else
	    {
		$list = $p->getElementsByTagName($this->tagSelector->getTag());
		for ( $i = 0 ; $i < $list->length ; $i++ )
		{
		    $e = $list->item($i);
		    if ( CSSHelper::checkSelector($e, $this->tagSelector) )
		    {
			$this->findAll($a, $e);
		    }
		}
	    }
	}
	else
	{
	    $this->findAll($a, $p);
	}

	return $a;
   }

   public function filter(ArrayList/*<DOMElement>*/ $elements)
   {
	$a = new ArrayList();
	foreach ( $elements->elements as $e )
	{
	    if ( empty($e) || !($e instanceof DOMElement) )
	    {
		continue;
	    }

	    if ( $this->isInContext($e) && CSSHelper::checkSelector($e, $this->selector) )
	    {
		$a->add($e);
	    }
	}

	return $e;
   }

//  ------------------------------------->

    private function isInContext(DOMElement $element)
    {
	if ( $this->context == $this->dom->documentElement )
	{
	    return true;
	}

	for ( $e = $element ; !empty($e) ; $e = $e->parentNode )
	{
	    if ( !($e instanceof DOMElement) )
	    {
		break;
	    }	

	    if ( $this->context == $e )
	    {
		return true;
	    }
	}

	return false;
    }

    private function findAll(ArrayList $a, DOMElement $parent, $tag="*")
    {
	$list = $parent->getElementsByTagName($tag);
	for ( $i = 0 ; $i < $list->length ; $i++ )
	{
	    $e = $list->item($i);
	    if ( CSSHelper::checkSelector($e, $this->selector) )
	    {
		self::addUnique($a, $e);
	    }
	}
    }

//  ------------------------------------->

    /**
     * Trouve des élements pour un selector
     */
    public static function findBySelector(DOMDocument $dom, CSSSelector $selector, /*DOMElement*/ $context=null)
    {
	$sel = new CSSDOMSelector($dom, $selector, $context);
	return $sel->find();
    }

    /**
     * Trouve des élements pour une liste selector
     */
    public static function findBySelectorList(DOMDocument $dom, ArrayList $list, /*DOMElement*/ $context=null)
    {
	$r = null;
	foreach ( $list->elements as $selector )
	{
	    $a = self::findBySelector($dom, $selector, $context);
	    if ( empty($r) )
	    {
		$r = $a;
	    }
	    else
	    {
		self::addAllUnique($r, $a);
	    }
	}

	if ( empty($r) )
	{
	    $r = new ArrayList();
	}

	return $r;
    }

    /**
     * Trouve des elements pour un ou plusieurs selecteurs 
     */
    public static function findBySelectorString(DOMDocument $dom, /*string*/ $s, /*DOMElement*/ $context=null)
    {
	return self::findBySelectorList($dom, CSSParser::parseSelector($s), $context);
    }

//  ------------------------------------->

    private static function addUnique(ArrayList/*<DOMElement>*/ $list, DOMElement $e)
    {
	if ( !$list->contains($e) )
	{
	    $list->add($e);
	}
    }

    private static function addAllUnique(ArrayList/*<DOMElement>*/ $list, ArrayList/*<DOMElement>*/ $a)
    {
	foreach ( $a->elements as $e )
	{
	    self::addUnique($list, $e);
	}
    }
}
?>
