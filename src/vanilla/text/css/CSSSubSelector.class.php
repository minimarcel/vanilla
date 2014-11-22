<?php

class CSSSubSelector
{
    // TODO css3 selectors (Exact, Contain, Begin, End)
    const MATCH_NONE		= 0;
    const MATCH_ID		= 1;
    const MATCH_CLASS		= 2;
    const MATCH_PSEUDO_CLASS	= 3;

//  ------------------------------------->

    private /*String*/ $value = null;
    private /*int*/ $match = self::MATCH_NONE;

//  ------------------------------------->

    public function __construct($value, $match)
    {
	$this->value = $value;
	$this->match = min(max(self::MATCH_NONE, $match), self::MATCH_PSEUDO_CLASS);
    }

//  ------------------------------------->
    
    public function getValue()
    {
	return $this->value;
    }

    public function getMatch()
    {
	return $this->match;
    }

    public function specificity()
    {
	if ( $this->match == self::MATCH_ID )
	{
	    return 0x10000;
	}

	return 0x100;
    }

//  ------------------------------------->

    public function __toString()
    {
	$s = "";
	switch ( $this->match )
	{
	    case self::MATCH_ID		    : $s .= "#"; break;
	    case self::MATCH_CLASS	    : $s .= "."; break;
	    case self::MATCH_PSEUDO_CLASS   : $s .= ":"; break;
	}

	$s .= $this->value;

	return $s;
    }
}
?>
