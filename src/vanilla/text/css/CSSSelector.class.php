<?php

import('vanilla.text.css.CSSSubSelector');

class CSSSelector
{
    const RELATION_DESCENDANT		= 0;
    const RELATION_CHILD		= 1;
    const RELATION_DIRECT_ADJACENT	= 2;
    const RELATION_INDIRECT_ADJACENT	= 3;

//  ------------------------------------->

    private /*String*/ $tag = null;
    private /*int*/ $relation = null;
    private /*CSSSelector*/ $tagHistory;
    private /*ArrayList*/ $subSelectors;

//  ------------------------------------->

    public function __construct($tag="")
    {
	$this->tag = strtolower(($tag == "*" ? "" : $tag));
	$this->relation = self::RELATION_DESCENDANT;
    }

//  ------------------------------------->

    public function addSubSelector(CSSSubSelector $sub)
    {
	if ( empty($this->subSelectors) )
	{
	    $this->subSelectors = new ArrayList();
	}

	$this->subSelectors->add($sub);
    }

    public function getSubSelectors()
    {
	return $this->subSelectors;
    }

    public function getTag()
    {
	return $this->tag;
    }

    public function getRelation()
    {
	return $this->relation;
    }

    public function getTagHistory()
    {
	return $this->tagHistory;
    }

    public function setTagHistory(CSSSelector $selector, $relation=0)
    {
	$this->tagHistory = $selector;
	$this->relation = min(max($relation, self::RELATION_DESCENDANT), self::RELATION_INDIRECT_ADJACENT);
    }

    public function specificity()
    {
	$i = 0;
	if ( !empty($this->tag) )
	{
	    $i++;   
	}

	if ( !empty($this->subSelectors) )
	{
	    foreach ( $this->subSelectors->elements as $sub )
	    {
		$i += $sub->specificity();
	    }
	}

	if ( !empty($this->tagHistory) )
	{
	    $i += $this->tagHistory->specificity();
	}

	return $i & 0xFFFFFF;
    }

    public function getSubSelectorTypeOfId()
    {
	return $this->getSubSelectorTypeOf(CSSSubSelector::MATCH_ID);
    }

    public function getSubSelectorTypeOfClass()
    {
	return $this->getSubSelectorTypeOf(CSSSubSelector::MATCH_CLASS);
    }

    public function getSubSelectorTypeOf($match)
    {
	if ( empty($this->subSelectors) )
	{
	    return null;
	}

	foreach ( $this->subSelectors->elements as $sub )
	{
	    if ( $sub->getMatch() == $match )
	    {
		return $sub;
	    }
	}

	return null;
    }

//  ------------------------------------->

    public function __toString()
    {
	$s = "";
	if ( empty($this->tag) && empty($this->subSelectors) )
	{
	    $s = "*";
	}

	if ( !empty($this->tag) )
	{
	    $s .= $this->tag;
	}

	if ( !empty($this->subSelectors) )
	{
	    foreach ( $this->subSelectors->elements as $sub )
	    {
		$s .= $sub;
	    }
	}

	if ( !empty($this->tagHistory) )
	{
	    switch($this->relation)
	    {
		case self::RELATION_DESCENDANT		: $s = " $s"; break;
		case self::RELATION_CHILD		: $s = " > $s"; break;
		case self::RELATION_DIRECT_ADJACENT	: $s = " + $s"; break;
		case self::RELATION_INDIRECT_ADJACENT	: $s = " ~ $s"; break;
	    }

	    $s = $this->tagHistory . $s;
	}

	return $s;
    }
}
?>
