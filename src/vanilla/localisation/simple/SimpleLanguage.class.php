<?php

import('vanilla.localisation.Language');

/**
 * 
 */
class SimpleLanguage implements Language
{
    private $code;
    private $label;

//  ---------------------------------------->

    function __construct($code, $label)
    {
	$this->code	= $code;
	$this->label	= $label;
    }

//  ---------------------------------------->

    public function getCode()
    {
	return $this->code;
    }

    public function getDefaultLabel()
    {
	return $this->label;
    }
}
?>
