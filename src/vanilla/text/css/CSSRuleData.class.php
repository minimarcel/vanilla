<?php

import("vanilla.util.Comparable");

class CSSRuleData implements Comparable
{
    private /*int*/ $position;
    private /*CSSStyleRule*/ $rule;
    private /*CSSSelector*/ $selector;

//  ------------------------------------->

    public function __construct($pos, CSSStyleRule $rule, CSSSelector $selector)
    {
	$this->position	    = $pos;
	$this->rule	    = $rule;
	$this->selector	    = $selector;
    }

//  ------------------------------------->

    public function getPosition()
    {
	return $this->position;
    }

    public function getRule()
    {
	return $this->rule;
    }

    public function getSelector()
    {
	return $this->selector;
    }

//  ------------------------------------->
    
    public function compareTo(Comparable $data)
    {
	if ( !($data instanceof CSSRuleData) )
	{
	    return -1;
	}

	$sp1	= $this->selector->specificity();
	$sp2	= $data->selector->specificity();
	$v	= 0;

	if ( $sp1 == $sp2 )
	{
	    $v = $this->position - $data->position;
	}
	else
	{
	    $v = $sp1 - $sp2;
	}

	if ( $v == 0 )
	{
	    return 0;
	}

	return ($v > 0 ? 1 : -1);
    }
}
?>
