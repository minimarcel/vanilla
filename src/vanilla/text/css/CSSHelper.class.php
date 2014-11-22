<?php

import('vanilla.text.css.CSSSelector');

class CSSHelper
{
    public static function checkSelector(DOMElement $element, CSSSelector $sel)
    {
	// on vérifie le tag
	$tag = $sel->getTag();
	if ( !empty($tag) && $tag != $element->nodeName )
	{
	    return false;
	}

	// on vérifie les subselectors
	$subSelectors = $sel->getSubSelectors();
	if ( !empty($subSelectors) )
	{
	    foreach ( $subSelectors->elements as $sub )
	    {
		$val = $sub->getValue();
		switch($sub->getMatch())
		{
		    case CSSSubSelector::MATCH_ID : 
		    {
			if ( $element->getAttribute("id") != $val )
			{
			    return false;
			}

			break;
		    }

		    case CSSSubSelector::MATCH_CLASS : 
		    {
			if ( !self::hasElementClassName($element, $val) )
			{
			    return false;
			}

			break;
		    }

		    case CSSSubSelector::MATCH_PSEUDO_CLASS : 
		    {
			return false;
		    }

		    // on ignore les autre pour l'instant
		}
	    }
	}

	// on gère la relation
	$history = $sel->getTagHistory();
	if ( empty($history) )
	{
	    return true;
	}

	switch($sel->getRelation())
	{
	    case CSSSelector::RELATION_DESCENDANT :
	    {

		$e = $element;
		while ( true )
		{
		    $parent = $e->parentNode;
		    if ( empty($parent) || !($parent instanceof DOMElement) )
		    {
			return false;
		    }

		    if ( self::checkSelector($parent, $history) )
		    {
			return true;
		    }

		    $e = $parent;
		}
	    }

	    case CSSSelector::RELATION_CHILD :
	    {
		$parent = $element->parentNode;
		if ( empty($parent) || !($parent instanceof DOMElement) )
		{
		    return false;
		}

		return self::checkSelector($parent, $history);
	    }

	    case CSSSelector::RELATION_DIRECT_ADJACENT :
	    {
		$sib = $element->previousSibling;
		while ( !empty($sib) && !($sib instanceof DOMElement) )
		{
		    $sib = $sib->previousSibling;
		}

		if ( empty($sib) )
		{
		    return false;
		}

		return self::checkSelector($sib, $history);
	    }

	    case CSSSelector::RELATION_INDIRECT_ADJACENT :
	    {
		$e = $element;
		while ( true )
		{
		    $sib = $e->previousSibling;
		    while ( !empty($sib) && !($sib instanceof DOMElement) )
		    {
			$sib = $sib->previousSibling;
		    }

		    if ( empty($sib) )
		    {
			return false;
		    }

		    if ( self::checkSelector($sib, $history) )
		    {
			return true;
		    }

		    $e = $sib;
		}
	    }
	}

	return false;
    }

    public static function hasElementClassName(DOMElement $elem, $className)
    {
	$classes = self::getElementClasses($elem);
	if ( empty($classes) )
	{
	    return false;
	}

	foreach ( $classes as $c )
	{
	    if ( $c == $className )
	    {
		return true;
	    }
	}

	return false;
    }

    public static function getElementClasses(DOMElement $elem)
    {
	$class = $elem->getAttribute("class");
	if ( !empty($class) )
	{
	    // on split les classes
	    return preg_split("/\s+/", $class);
	}

	return null;
    }
}
