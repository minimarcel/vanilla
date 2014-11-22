<?php

import('vanilla.text.css.CSSDeclaration');
import('vanilla.text.css.CSSSelector');

/*
    TODO extends rule
    a rule can be a media rule or an import rule
*/
class CSSStyleRule
{
    private /*ArrayList*/ $selectors;
    private /*CSSDeclaration*/ $declaration;

//  ------------------------------------->

    public function __construct()
    {
	$this->selectors = new ArrayList();
    }

//  ------------------------------------->

    public function getSelectors()
    {
	return $this->selectors;
    }

    public function addSelector(CSSSelector $selector)
    {
	$this->selectors->add($selector);
    }

    public function setDeclaration(CSSDeclaration $decl)
    {
	$this->declaration = $decl;
    }

    public function getDeclaration()
    {
	return $this->declaration;
    }

//  ------------------------------------->

    public function __toString()
    {
	$s = "";
	foreach ( $this->selectors->elements as $selector )
	{
	    $s .= (empty($s) ? "" : ", ") . "$selector";
	}

	$s .= "{" . $this->declaration . "}";

	return $s;
    }
}
?>
